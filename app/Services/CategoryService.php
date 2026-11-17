<?php

namespace App\Services;

use App\Enums\CategoryStatus;
use App\Models\Category;

class CategoryService
{
    public function create(array $data): Category
    {
        return Category::create([
            ...$data,
            'status' => CategoryStatus::Activo,
        ]);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }
}
