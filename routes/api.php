<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AccountController;
use Illuminate\Support\Facades\Route;

Route::apiResource('clients', ClientController::class);
Route::post('clients/bulk', [ClientController::class, 'bulkStore']);

Route::apiResource('accounts', AccountController::class);
Route::post('accounts/bulk', [AccountController::class, 'bulkStore']);
