<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\BillerItemController;
use App\Http\Controllers\Api\AccountMovementController;
use App\Http\Controllers\Api\AmortizationController;
use App\Http\Controllers\Api\BillerController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

// Entidades simples: CRUD completo.
Route::apiResource('clients', ClientController::class);
Route::apiResource('accounts', AccountController::class);
Route::apiResource('products', ProductController::class);

// categories: se administran mediante estado; no se eliminan físicamente.
Route::get('categories/protected', [CategoryController::class, 'protectedCategories']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show', 'store', 'update']);

// billers: index/show/store. Sin update/destroy (ver nota en el controller).
Route::apiResource('billers', BillerController::class)->only(['index', 'show', 'store']);

// stock_movements: solo index/show/store (store = únicamente entradas manuales).
Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'show', 'store']);

// biller_items: solo lectura, se crean dentro de BillerService.
Route::apiResource('biller-items', BillerItemController::class)->only(['index', 'show']);

// account_movements: solo lectura para listar/consultar.
Route::apiResource('account-movements', AccountMovementController::class)->only(['index', 'show']);

// Acción de negocio puntual (no es CRUD): registrar una amortización sobre
// la deuda pendiente de una factura al crédito ya existente.
Route::post('billers/{biller}/amortizations', AmortizationController::class);
