<?php

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', CategoryType::values());
            $table->enum('status', CategoryStatus::values())->default(CategoryStatus::Activo->value);
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
