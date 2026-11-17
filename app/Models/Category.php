<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public const SALES_NAME = 'Ventas';
    public const SERVICE_PAYMENT_NAME = 'Pago de servicios';
    public const UNFORESEEN_NAME = 'Imprevistos';

    public const PROTECTED_NAMES = [
        self::SALES_NAME,
        self::SERVICE_PAYMENT_NAME,
        self::UNFORESEEN_NAME,
    ];

    protected $fillable = [
        'name',
        'type',
        'status',
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
            ->firstOrFail()
            ->getKey();
    }

    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED_NAMES, true);
    }

    public function scopeProtectedCategories(Builder $query): void
    {
        $query->whereIn('name', self::PROTECTED_NAMES);
    }

    public function accountMovements(): HasMany
    {
        return $this->hasMany(AccountMovement::class);
    }
}
