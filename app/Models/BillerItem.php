<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillerItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'biller_id',
        'storage_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function biller()
    {
        return $this->belongsTo(Biller::class);
    }

    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }
}
