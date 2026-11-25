<?php

namespace App\Services;

use App\Enums\AccountMovementStatus;
use App\Enums\AccountMovementType;
use App\Enums\PriceTier;
use App\Enums\SaleType;
use App\Enums\StockMovementType;
use App\Models\AccountMovement;
use App\Models\Account;
use App\Models\Biller;
use App\Models\BillerItem;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillerService
{
    public function __construct(private readonly AccountMovementService $accountMovements)
    {
    }

    /**
     * Crea una factura completa: sus items, el StockMovement de salida de
     * cada producto, y el/los AccountMovement según sea al contado o al
     * crédito. Todo en una sola transacción — si algo falla a mitad de
     * camino (ej. stock insuficiente), no queda nada a medio crear.
     */
    public function create(User $user, array $data): Biller
    {
        return DB::transaction(function () use ($user, $data) {
            $saleType = $this->normalizeSaleType($data['sale_type']);
            $this->assertRelationsBelongToUser($user, $data);

            // lockForUpdate en resolveLines() bloquea las filas de producto
            // involucradas hasta que termine la transacción: si dos ventas
            // del mismo producto llegan casi al mismo tiempo, la segunda
            // espera a que la primera termine de descontar stock, en vez de
            // que ambas lean el mismo stock "viejo" y lo dejen negativo.
            $lines = $this->resolveLines($user, $data['items']);
            $this->assertStockIsAvailable($lines);

            $this->assertAmountsAreValid($data, $lines);

            $subtotal = (float) $data['subtotal'];
            $igvAmount = (float) $data['igv_amount'];
            $total = (float) $data['total'];
            $salesCategoryId = Category::salesId();

            $this->assertInitialPaymentIsValid($saleType, $data, $total);

            $biller = Biller::create([
                'sale_date' => $data['sale_date'],
                'paid_at' => $saleType === SaleType::Contado ? $data['sale_date'] : null,
                'due_date' => $saleType === SaleType::Credito
                    ? ($data['due_date'] ?? null)
                    : null,
                'sale_type' => $saleType,
                'subtotal' => $subtotal,
                'igv_amount' => $igvAmount,
                'igv_percentage' => $data['igv_percentage'] ?? null,
                'total' => $total,
                'client_id' => $data['client_id'],
                'user_id' => $user->id,
                // Solo se guarda aquí en 'contado' (una sola cuenta, un solo
                // pago, inmediato). En 'credito' la cuenta vive por cada
                // AccountMovement (pueden ser cuentas distintas en cada
                // amortización), así que este campo queda null.
                'account_id' => $saleType === SaleType::Contado ? $data['account_id'] : null,
            ]);

            $this->createItemsAndStockMovements($biller, $lines);

            $saleType === SaleType::Contado
                ? $this->registerCashPayment($biller, $data['account_id'], $total, $salesCategoryId)
                : $this->registerCreditSale($biller, $total, $data, $salesCategoryId);

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
    private function resolveLines(User $user, array $items): array
    {
        $products = Product::where('user_id', $user->id)
            ->whereIn('id', array_column($items, 'product_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== count(array_unique(array_column($items, 'product_id')))) {
            throw ValidationException::withMessages([
                'items' => 'Uno o más productos no pertenecen al usuario autenticado.',
            ]);
        }

        return array_map(function (array $item, int $index) use ($products) {
            /** @var Product $product */
            $product = $products[$item['product_id']];
            $tier = $item['price_tier'] instanceof PriceTier
                ? $item['price_tier']
                : PriceTier::from($item['price_tier']);

            $quantity = (float) $item['quantity'];
            $unitPrice = round((float) $item['unit_price'], 2);
            $expectedUnitPrice = round($tier->resolvePrice($product), 2);

            if ($unitPrice !== $expectedUnitPrice) {
                throw ValidationException::withMessages([
                    "items.$index.unit_price" => "El precio unitario debe ser {$expectedUnitPrice}.",
                ]);
            }

            return [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'sell_percentage' => $tier->resolvePercentage($product),
                'type_percentage' => $tier,
                'subtotal' => round($unitPrice * $quantity, 2),
            ];
        }, $items, array_keys($items));
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

    private function assertAmountsAreValid(array $data, array $lines): void
    {
        $expectedSubtotal = $this->calculateSubtotal($lines);
        $igvPercentage = array_key_exists('igv_percentage', $data)
            ? (float) $data['igv_percentage']
            : null;
        $expectedIgv = $igvPercentage === null
            ? 0.0
            : round($expectedSubtotal * $igvPercentage / 100, 2);
        $expectedTotal = round($expectedSubtotal + $expectedIgv, 2);

        $errors = [];

        if (round((float) $data['subtotal'], 2) !== $expectedSubtotal) {
            $errors['subtotal'] = "El subtotal debe ser {$expectedSubtotal}.";
        }

        if (round((float) $data['igv_amount'], 2) !== $expectedIgv) {
            $errors['igv_amount'] = "El IGV debe ser {$expectedIgv}.";
        }

        if (round((float) $data['total'], 2) !== $expectedTotal) {
            $errors['total'] = "El total debe ser {$expectedTotal}.";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertInitialPaymentIsValid(SaleType $saleType, array $data, float $total): void
    {
        if ($saleType === SaleType::Credito && ! empty($data['initial_payment']) && $data['initial_payment'] > $total) {
            throw ValidationException::withMessages([
                'initial_payment' => "El pago inicial no puede superar el total de la venta ({$total}).",
            ]);
        }
    }

    private function assertRelationsBelongToUser(User $user, array $data): void
    {
        if (! Client::query()->whereKey($data['client_id'])->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'client_id' => 'El cliente no pertenece al usuario autenticado.',
            ]);
        }

        if (
            isset($data['account_id'])
            && ! Account::query()->whereKey($data['account_id'])->where('user_id', $user->id)->exists()
        ) {
            throw ValidationException::withMessages([
                'account_id' => 'La cuenta no pertenece al usuario autenticado.',
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
                'sell_percentage' => $line['sell_percentage'],
                'type_percentage' => $line['type_percentage'],
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
                'user_id' => $biller->user_id,
            ]);
        }
    }

    private function registerCashPayment(Biller $biller, int $accountId, float $total, int $categoryId): void
    {
        AccountMovement::create([
            'amount' => $total,
            'movement_date' => now(),
            'type' => AccountMovementType::Ingreso,
            'status' => AccountMovementStatus::CuentaCobrada,
            'description' => 'Pago al contado',
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'biller_id' => $biller->id,
            'user_id' => $biller->user_id,
        ]);
    }

    private function registerCreditSale(Biller $biller, float $total, array $data, int $categoryId): void
    {
        // Nace la deuda completa como 'cuenta_por_cobrar', sin cuenta
        // asignada todavía (regla que ya habíamos definido).
        AccountMovement::create([
            'amount' => $total,
            'movement_date' => now(),
            'type' => AccountMovementType::Ingreso,
            'status' => AccountMovementStatus::CuentaPorCobrar,
            'description' => 'Saldo pendiente por cobrar',
            'category_id' => $categoryId,
            'account_id' => null,
            'biller_id' => $biller->id,
            'user_id' => $biller->user_id,
        ]);

        // Si mandaron un pago inicial, reutilizamos EXACTAMENTE la misma
        // lógica de amortización que ya construimos para amortizaciones
        // posteriores — no la duplicamos aquí. AccountMovementService busca
        // el registro 'cuenta_por_cobrar' que acabamos de crear (mismo id
        // de transacción, así que ya es visible) y descuenta este pago.
        if (! empty($data['initial_payment'])) {
            $this->accountMovements->registerAmortization($biller->user, $biller, [
                'account_id' => $data['account_id'],
                'amount' => $data['initial_payment'],
                'description' => 'Pago inicial al momento de la venta',
            ]);
        }
    }
}
