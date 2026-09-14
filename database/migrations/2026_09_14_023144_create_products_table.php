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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('brand')->nullable();
            $table->string('measure_unity', 20);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('percentage_distributor', 5, 2)->default(0);
            $table->decimal('price_distributor', 10, 2)->default(0);
            $table->decimal('percentage_major', 5, 2)->default(0);
            $table->decimal('price_major', 10, 2)->default(0);
            $table->decimal('percentage_general', 5, 2)->default(0);
            $table->decimal('price_general', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
