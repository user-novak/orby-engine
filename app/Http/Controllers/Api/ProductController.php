<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\IndexProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->latest('id')
                ->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $this->products->create($request->user(), $request->validated());

        return ProductResource::make($product);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        abort_unless($product->user_id === $request->user()->id, 404);

        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->products->update($product, $request->validated());

        return ProductResource::make($product);
    }

    public function destroy(Request $request, Product $product): Response
    {
        abort_unless($product->user_id === $request->user()->id, 404);

        $this->products->delete($product);

        return response()->noContent();
    }
}
