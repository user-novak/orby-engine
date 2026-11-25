<?php

namespace App\Services;

use App\Enums\CategoryStatus;
use App\Models\Category;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function create(User $user, array $data): Category
    {
        $this->assertNameIsAvailable($user, $data['name']);

        return $user->categories()->create([
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

        if (array_key_exists('name', $data)) {
            $this->assertNameIsAvailable($category->user, $data['name'], $category);
        }

        $category->update($data);

        return $category->refresh();
    }

    private function assertNameIsAvailable(
        User $user,
        string $name,
        ?Category $ignoredCategory = null,
    ): void {
        $exists = Category::query()
            ->where('name', $name)
            ->where(function ($query) use ($user) {
                $query
                    ->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->when(
                $ignoredCategory !== null,
                fn ($query) => $query->where('id', '!=', $ignoredCategory->id)
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Ya existe una categoría con este nombre.',
            ]);
        }
    }
}
