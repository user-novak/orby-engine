<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountMovement extends Model
{
    protected $fillable = [
        'amount',
        'movement_date',
        'type',
        'status',
        'description',
        'account_id',
        'biller_id',
    ];

    /**
     * Transforma automáticamente los atributos a un formato decimal y fecha
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'movement_date' => 'datetime',
        ];
    }

    /**
     * Nullable: al crear el "pago pendiente" aún no se sabe a qué cuenta
     * va a ingresar el dinero.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }
}
