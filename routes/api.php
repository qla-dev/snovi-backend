<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\StoryController;

Route::get('/ping', fn () => [
    'ok' => true,
    'timestamp' => now()->toIso8601String(),
]);

Route::apiResource('categories', CategoryController::class);
Route::apiResource('subcategories', SubcategoryController::class);
Route::get('/stories/published', [StoryController::class, 'recentPublished']);
Route::get('/stories/tenrecent', [StoryController::class, 'recentPublished']);
Route::apiResource('stories', StoryController::class);
