<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories)
    {
    }

    public function index(IndexCategoryRequest $request): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->where(function ($query) use ($request) {
                $query
                    ->whereNull('user_id')
                    ->orWhere('user_id', $request->user()->id);
            })
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', $request->string('type'))
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status'))
            )
            ->latest()
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $category = $this->categories->create($request->user(), $request->validated());

        return CategoryResource::make($category);
    }

    public function protectedCategories(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            Category::query()
                ->protectedCategories()
                ->oldest('id')
                ->get()
        );
    }

    public function show(Request $request, Category $category): CategoryResource
    {
        abort_unless(
            $category->user_id === null || $category->user_id === $request->user()->id,
            404,
        );

        return CategoryResource::make($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category = $this->categories->update($category, $request->validated());

        return CategoryResource::make($category);
    }
}
