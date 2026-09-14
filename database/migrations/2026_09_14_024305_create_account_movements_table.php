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
        Schema::create('account_movements', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->dateTime('movement_date');
            $table->enum('type', ['ingreso', 'egreso']);
            $table->enum('status', [
                'cuenta_cobrada',
                'cuenta_pagada',
                'cuenta_por_cobrar',
                'cuenta_por_pagar',
                'amortizacion',
            ]);
            $table->text('description')->nullable();
            $table->foreignId('account_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('biller_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_movements');
    }
};
