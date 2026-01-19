<?php

use App\Http\Controllers\Api\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;



Route::get('/users/{user_id}/accounts', [AccountController::class, 'index']);
Route::post('/accounts', [AccountController::class, 'store']);
Route::get('/accounts/{id}', [AccountController::class, 'show']);
Route::patch('/accounts/{id}/deactivate', [AccountController::class, 'deactivate']);



Route::post('/deposit', [TransactionController::class, 'deposit']);
Route::post('/withdraw', [TransactionController::class, 'withdraw']);
Route::post('/transfer', [TransactionController::class, 'transfer']);

Route::get('/accounts/{account_id}/transactions', [TransactionController::class, 'history']);
