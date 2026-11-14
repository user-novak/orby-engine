<?php

namespace Database\Seeders;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => Category::SALES_NAME,
                'type' => CategoryType::Ingreso,
                'status' => CategoryStatus::Activo,
            ],
            [
                'name' => Category::SERVICE_PAYMENT_NAME,
                'type' => CategoryType::Egreso,
                'status' => CategoryStatus::Activo,
            ],
            [
                'name' => Category::UNFORESEEN_NAME,
                'type' => CategoryType::Ambos,
                'status' => CategoryStatus::Activo,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                $category,
            );
        }
    }
}
