<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('biller_payment_amortizations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('payment_date');
            $table->decimal('amount', 12, 2);
            $table->foreignId('biller_payment_id');
            $table->foreignId('client_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biller_payment_amortizations');
    }
};
