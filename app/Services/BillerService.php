<?php

namespace App\Services;

use App\Enums\AccountMovementStatus;
use App\Enums\AccountMovementType;
use App\Enums\PriceTier;
use App\Enums\SaleType;
use App\Enums\StockMovementType;
use App\Models\AccountMovement;
use App\Models\Biller;
use App\Models\BillerItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillerService
{
    /**
     * IGV vigente en Perú. Único lugar donde vive esta tasa: si cambia,
     * se actualiza aquí y ya. (Si algún día necesitas que sea configurable
     * por entorno, pásala a config/billing.php y léela con config().)
     */
    private const IGV_RATE = 0.18;

    public function __construct(private readonly AccountMovementService $accountMovements)
    {
    }

    /**
     * Crea una factura completa: sus items, el StockMovement de salida de
     * cada producto, y el/los AccountMovement según sea al contado o al
     * crédito. Todo en una sola transacción — si algo falla a mitad de
     * camino (ej. stock insuficiente), no queda nada a medio crear.
     */
    public function create(array $data): Biller
    {
        return DB::transaction(function () use ($data) {
            $saleType = $this->normalizeSaleType($data['sale_type']);

            // lockForUpdate en resolveLines() bloquea las filas de producto
            // involucradas hasta que termine la transacción: si dos ventas
            // del mismo producto llegan casi al mismo tiempo, la segunda
            // espera a que la primera termine de descontar stock, en vez de
            // que ambas lean el mismo stock "viejo" y lo dejen negativo.
            $lines = $this->resolveLines($data['items']);
            $this->assertStockIsAvailable($lines);

            $subtotal = $this->calculateSubtotal($lines);
            $igv = round($subtotal * self::IGV_RATE, 2);
            $total = round($subtotal + $igv, 2);

            $this->assertInitialPaymentIsValid($saleType, $data, $total);

            $biller = Biller::create([
                'sale_date' => $data['sale_date'],
                'payment_date' => $data['payment_date']
                    ?? ($saleType === SaleType::Contado ? $data['sale_date'] : null),
                'sale_type' => $saleType,
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'client_id' => $data['client_id'],
                // Solo se guarda aquí en 'contado' (una sola cuenta, un solo
                // pago, inmediato). En 'credito' la cuenta vive por cada
                // AccountMovement (pueden ser cuentas distintas en cada
                // amortización), así que este campo queda null.
                'account_id' => $saleType === SaleType::Contado ? $data['account_id'] : null,
            ]);

            $this->createItemsAndStockMovements($biller, $lines);

            $saleType === SaleType::Contado
                ? $this->registerCashPayment($biller, $data['account_id'], $total)
                : $this->registerCreditSale($biller, $total, $data);

            return $biller->refresh()->load(['client', 'items.product', 'accountMovements']);
        });
    }

    private function normalizeSaleType(SaleType|string $saleType): SaleType
    {
        return $saleType instanceof SaleType ? $saleType : SaleType::from($saleType);
    }

    /**
     * Convierte cada item del request en una línea "resuelta": el Product
     * real (bloqueado con lockForUpdate), su unit_price según el price_tier
     * de esa línea, y el subtotal ya calculado.
     */
    private function resolveLines(array $items): array
    {
        $products = Product::whereIn('id', array_column($items, 'product_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        return array_map(function (array $item) use ($products) {
            /** @var Product $product */
            $product = $products[$item['product_id']];
            $tier = $item['price_tier'] instanceof PriceTier
                ? $item['price_tier']
                : PriceTier::from($item['price_tier']);

            $quantity = (float) $item['quantity'];
            $unitPrice = $tier->resolvePrice($product);

            return [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($unitPrice * $quantity, 2),
            ];
        }, $items);
    }

    /**
     * Segunda validación de stock (la primera, "amigable", ya la hizo
     * StoreBillerRequest). Esta es la que realmente cuenta: corre después
     * del lockForUpdate de resolveLines(), así que ningún otro proceso pudo
     * haber cambiado el stock entre medio.
     */
    private function assertStockIsAvailable(array $lines): void
    {
        foreach ($lines as $line) {
            if ($line['quantity'] > $line['product']->stock) {
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para \"{$line['product']->description}\" (disponible: {$line['product']->stock}).",
                ]);
            }
        }
    }

    private function calculateSubtotal(array $lines): float
    {
        return round(array_sum(array_column($lines, 'subtotal')), 2);
    }

    private function assertInitialPaymentIsValid(SaleType $saleType, array $data, float $total): void
    {
        if ($saleType === SaleType::Credito && ! empty($data['initial_payment']) && $data['initial_payment'] > $total) {
            throw ValidationException::withMessages([
                'initial_payment' => "El pago inicial no puede superar el total de la venta ({$total}).",
            ]);
        }
    }

    private function createItemsAndStockMovements(Biller $biller, array $lines): void
    {
        foreach ($lines as $line) {
            BillerItem::create([
                'biller_id' => $biller->id,
                'product_id' => $line['product']->id,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'subtotal' => $line['subtotal'],
            ]);

            // StockMovementObserver descuenta products.stock automáticamente
            // al detectar el 'created' de este registro — no lo tocamos acá.
            StockMovement::create([
                'type' => StockMovementType::Salida,
                'quantity' => $line['quantity'],
                'movement_date' => now(),
                'product_id' => $line['product']->id,
                'biller_id' => $biller->id,
            ]);
        }
    }

    private function registerCashPayment(Biller $biller, int $accountId, float $total): void
    {
        AccountMovement::create([
            'amount' => $total,
            'movement_date' => now(),
            'type' => AccountMovementType::Ingreso,
            'status' => AccountMovementStatus::CuentaCobrada,
            'description' => 'Pago al contado',
            'account_id' => $accountId,
            'biller_id' => $biller->id,
        ]);
    }

    private function registerCreditSale(Biller $biller, float $total, array $data): void
    {
        // Nace la deuda completa como 'cuenta_por_cobrar', sin cuenta
        // asignada todavía (regla que ya habíamos definido).
        AccountMovement::create([
            'amount' => $total,
            'movement_date' => now(),
            'type' => AccountMovementType::Ingreso,
            'status' => AccountMovementStatus::CuentaPorCobrar,
            'description' => 'Saldo pendiente por cobrar',
            'account_id' => null,
            'biller_id' => $biller->id,
        ]);

        // Si mandaron un pago inicial, reutilizamos EXACTAMENTE la misma
        // lógica de amortización que ya construimos para amortizaciones
        // posteriores — no la duplicamos aquí. AccountMovementService busca
        // el registro 'cuenta_por_cobrar' que acabamos de crear (mismo id
        // de transacción, así que ya es visible) y descuenta este pago.
        if (! empty($data['initial_payment'])) {
            $this->accountMovements->registerAmortization($biller, [
                'account_id' => $data['account_id'],
                'amount' => $data['initial_payment'],
                'description' => 'Pago inicial al momento de la venta',
            ]);
        }
    }
}
