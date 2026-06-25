<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::apiResource('categories', CategoryController::class)->only([
    'index',
    'show',
]);

Route::apiResource('categories', CategoryController::class)->only([
    'store',
    'update',
    'destroy',
])->middleware('auth:sanctum');

Route::apiResource('properties', PropertyController::class)->only([
    'index',
    'show',
]);

Route::apiResource('properties', PropertyController::class)->only([
    'store',
    'update',
    'destroy',
])->middleware('auth:sanctum');

Route::apiResource('inquiries', InquiryController::class)->only([
    'index',
    'store',
    'update',
])->middleware('auth:sanctum');
