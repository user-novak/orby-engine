<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\StoreStorageRequest;
use App\Http\Requests\Storage\UpdateStorageRequest;
use App\Http\Requests\Storage\BulkStoreStorageRequest;
use App\Models\Storage;
use App\Traits\ApiResponse;

class StorageController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(
            Storage::all(),
            'Almacenes obtenidos correctamente'
        );
    }

    public function store(StoreStorageRequest $request)
    {
        $storage = Storage::create($request->validated());

        return $this->success(
            $storage,
            'Producto almacenado correctamente',
            201
        );
    }

    public function bulkStore(BulkStoreStorageRequest $request)
    {
        Storage::insert($request->validated()['storages']);

        return $this->success(
            null,
            'Productos registrados correctamente',
            201
        );
    }

    public function show(Storage $storage)
    {
        return $this->success(
            $storage,
            'Producto obtenido correctamente'
        );
    }

    public function update(UpdateStorageRequest $request, Storage $storage)
    {
        $storage->update($request->validated());

        return $this->success(
            $storage,
            'Producto actualizado correctamente'
        );
    }

    public function destroy(Storage $storage)
    {
        $storage->delete();

        return $this->success(
            null,
            'Producto eliminado correctamente',
            204
        );
    }
}
