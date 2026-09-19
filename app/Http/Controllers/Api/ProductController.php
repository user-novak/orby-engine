<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()->latest()->paginate()
        );
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $this->products->create($request->validated());

        return ProductResource::make($product);
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->products->update($product, $request->validated());

        return ProductResource::make($product);
    }

    public function destroy(Product $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
