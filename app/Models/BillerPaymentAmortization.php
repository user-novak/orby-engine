<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillerPaymentAmortization extends Model
{
    use HasFactory;

    protected $fillable = [
        'biller_payment_id',
        'client_id',
        'amount',
        'payment_date',
    ];

    public function payment()
    {
        return $this->belongsTo(BillerPayment::class, 'biller_payment_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
