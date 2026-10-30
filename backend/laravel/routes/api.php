<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);

// مسارات التصنيفات العامة

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// تسجيل دخول المدير

Route::post('/admin/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1');

// مسارات تتطلب تسجيل الدخول

Route::middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {

    Route::post('/admin/logout', [AuthController::class, 'logout']);

    Route::get('/admin/me', [AuthController::class, 'me']);

    // إدارة التصنيفات
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::delete('/products/{product}', [ProductController::class, 'delete']);

});
