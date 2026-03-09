<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\PrixCatalogueController;
use App\Http\Controllers\Api\ProjectArticleController;
use App\Http\Controllers\Api\ProjectPrixController;
use App\Http\Controllers\Api\ProjetController;
use Illuminate\Support\Facades\Route;

Route::get('status', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'OK', 'db' => 'Connected']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'Error', 'db' => 'Disconnected'], 500);
    }
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

Route::get('exports/{export}/download', [ExportController::class, 'download'])->name('exports.download');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    Route::get('catalogue-articles/categories', [PrixCatalogueController::class, 'categories']);
    Route::apiResource('catalogue-articles', PrixCatalogueController::class)->parameters([
        'catalogue-articles' => 'prixCatalogue',
    ]);

    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('projets', ProjetController::class);

    Route::prefix('projets/{projet}')->group(function () {
        Route::get('articles',                     [ProjectArticleController::class, 'index']);
        Route::post('articles',                    [ProjectArticleController::class, 'store']);
        Route::put('articles/{projectArticle}',    [ProjectArticleController::class, 'update']);
        Route::delete('articles/{projectArticle}', [ProjectArticleController::class, 'destroy']);

        Route::get('prix',                  [ProjectPrixController::class, 'index']);
        Route::post('prix',                 [ProjectPrixController::class, 'store']);
        Route::put('prix/{projectPrix}',    [ProjectPrixController::class, 'update']);
        Route::delete('prix/{projectPrix}', [ProjectPrixController::class, 'destroy']);

        Route::post('exports/cps',  [ExportController::class, 'exportCps']);
        Route::post('exports/rc',   [ExportController::class, 'exportRc']);
        Route::post('exports/brd',  [ExportController::class, 'exportBrd']);
        Route::get('exports',       [ExportController::class, 'listExports']);
    });

    Route::delete('exports/{export}', [ExportController::class, 'destroy']);
});
