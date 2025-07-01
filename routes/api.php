<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PornstarController;
use App\Http\Controllers\PornstarAliasController;
use App\Http\Controllers\PornstarThumbnailController;
use App\Http\Controllers\PornstarThumbnailUrlController;

Route::prefix('v1')->group(function () {
    Route::apiResource('pornstars', PornstarController::class);
    Route::apiResource('pornstar-aliases', PornstarAliasController::class);
    Route::apiResource('pornstar-thumbnails', PornstarThumbnailController::class);
    Route::apiResource('pornstar-thumbnail-urls', PornstarThumbnailUrlController::class);

    // Health + Version
    Route::get('health', fn () => response()->json(['status' => 'ok']));
    Route::get('version', fn () => response()->json(['version' => config('app.version', 'v1.0.0')]));
});
