<?php

namespace App\Services;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

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
        if ($category->isProtected()) {
            throw ValidationException::withMessages([
                'category' => 'Las categorías predeterminadas no se pueden modificar ni desactivar.',
            ]);
        }

        $category->update($data);

        return $category->refresh();
    }
}
