<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExternalDataController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/external/geocode', [ExternalDataController::class, 'geocode']);
Route::get('/external/weather', [ExternalDataController::class, 'weather']);

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

Route::get('/categories/{category}/properties', [PropertyController::class, 'byCategory']);

Route::apiResource('properties', PropertyController::class)->only([
    'index',
    'show',
]);

Route::get('/properties/{property}/location', [ExternalDataController::class, 'propertyLocation']);
Route::get('/properties/{property}/weather', [ExternalDataController::class, 'propertyWeather']);

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

Route::get('/properties/{property}/inquiries', [InquiryController::class, 'byProperty'])
    ->middleware('auth:sanctum');
