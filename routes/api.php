<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\BillerController;
use Illuminate\Support\Facades\Route;

Route::apiResource('clients', ClientController::class);
Route::post('clients/bulk', [ClientController::class, 'bulkStore']);

Route::apiResource('accounts', AccountController::class);
Route::post('accounts/bulk', [AccountController::class, 'bulkStore']);

Route::apiResource('storages', StorageController::class);
Route::post('storages/bulk', [StorageController::class, 'bulkStore']);

Route::get('biller/data', [BillerController::class, 'index']);
Route::post('biller', [BillerController::class, 'store']);
