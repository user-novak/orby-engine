<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'name',
        'account_number',
        'amount',
        'description',
    ];

    /**
     * Transforma automáticamente el atributo 'amount' a un formato decimal
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Una cuenta puede estar asociada a muchas facturas (1:N).
     */
    public function billers(): HasMany
    {
        return $this->hasMany(Biller::class);
    }

    /**
     * Una cuenta puede tener muchos movimientos (1:N).
     */
    public function movements(): HasMany
    {
        return $this->hasMany(AccountMovement::class);
    }
}
