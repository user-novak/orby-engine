<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'dni',
        'name',
        'ruc',
        'phone',
        'address'
    ];

    public function amortizations()
    {
        return $this->hasMany(BillerPaymentAmortization::class);
    }

    public function billers()
    {
        return $this->hasMany(Biller::class);
    }

    public function billerPayments()
    {
        return $this->hasMany(BillerPayment::class);
    }
}
