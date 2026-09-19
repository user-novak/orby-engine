<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('clients', ClientController::class);
Route::apiResource('accounts', AccountController::class);
Route::apiResource('products', ProductController::class);
