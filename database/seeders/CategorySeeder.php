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
                'user_id' => null,
            ],
            [
                'name' => Category::SERVICE_PAYMENT_NAME,
                'type' => CategoryType::Egreso,
                'status' => CategoryStatus::Activo,
                'user_id' => null,
            ],
            [
                'name' => Category::UNFORESEEN_NAME,
                'type' => CategoryType::Ambos,
                'status' => CategoryStatus::Activo,
                'user_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                [
                    'name' => $category['name'],
                    'user_id' => null,
                ],
                $category,
            );
        }
    }
}
