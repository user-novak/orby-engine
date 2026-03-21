<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = [
        'description',
        'brand',
        'measure_unity',
        'unit_price',
        'percentage_distributor',
        'price_distributor',
        'percentage_major',
        'price_major',
        'percentage_general',
        'price_general',
        'input',
        'output',
        'stock',
    ];

    public function billerItems()
    {
        return $this->hasMany(BillerItem::class);
    }
}
