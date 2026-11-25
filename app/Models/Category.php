<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public const SALES_NAME = 'Ventas';
    public const SERVICE_PAYMENT_NAME = 'Pago de servicios';
    public const UNFORESEEN_NAME = 'Imprevistos';

    protected $fillable = [
        'name',
        'type',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'status' => CategoryStatus::class,
        ];
    }

    public static function salesId(): int
    {
        return (int) self::query()
            ->where('name', self::SALES_NAME)
            ->where('type', CategoryType::Ingreso->value)
            ->where('status', CategoryStatus::Activo->value)
            ->whereNull('user_id')
            ->firstOrFail()
            ->getKey();
    }

    public function isProtected(): bool
    {
        return $this->user_id === null;
    }

    public function scopeProtectedCategories(Builder $query): void
    {
        $query->whereNull('user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountMovements(): HasMany
    {
        return $this->hasMany(AccountMovement::class);
    }
}
