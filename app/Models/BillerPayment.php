<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'biller_id',
        'client_id',
        'amount',
        'payment_date'
    ];

    public function biller()
    {
        return $this->belongsTo(Biller::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
