<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'dni',
        'name',
        'ruc',
        'phone',
        'address',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un cliente puede tener muchas facturas (1:N).
     */
    public function billers(): HasMany
    {
        return $this->hasMany(Biller::class);
    }
}
