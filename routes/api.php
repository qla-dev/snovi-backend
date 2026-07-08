<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GiftCodeController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\PushNotificationController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\StoryController;

Route::get('/ping', fn () => [
    'ok' => true,
    'timestamp' => now()->toIso8601String(),
]);

Route::get('/notifications/default', [PushNotificationController::class, 'default']);
Route::post('/push-tokens', [PushTokenController::class, 'store']);
Route::post('/gift-codes/redeem', [GiftCodeController::class, 'redeem']);
Route::post('/gift-codes/revoke', [GiftCodeController::class, 'revoke']);
Route::get('/categories/search', [CategoryController::class, 'search']);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('music', MusicController::class);
Route::apiResource('subcategories', SubcategoryController::class);
Route::get('/stories/free', [StoryController::class, 'freeSongs']);
Route::get('/stories/published', [StoryController::class, 'recentPublished']);
Route::get('/stories/tenrecent', [StoryController::class, 'recentPublished']);
Route::apiResource('stories', StoryController::class);
