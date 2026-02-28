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
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('brand');
            $table->string('measure_unity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('percentage_distributor', 5, 2);
            $table->decimal('price_distributor', 15, 2);
            $table->decimal('percentage_major', 5, 2);
            $table->decimal('price_major', 15, 2);
            $table->decimal('percentage_general', 5, 2);
            $table->decimal('price_general', 15, 2);
            $table->integer('input');
            $table->integer('output');
            $table->integer('stock');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storages');
    }
};
