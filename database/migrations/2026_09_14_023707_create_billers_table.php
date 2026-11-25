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
        Schema::create('billers', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->timestamp('paid_at')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('sale_type', ['contado', 'credito']);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv_amount', 10, 2)->default(0);
            $table->decimal('igv_percentage', 5, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billers');
    }
};
