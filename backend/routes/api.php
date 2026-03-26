<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\PrixCatalogueController;
use App\Http\Controllers\Api\ProjectArticleController;
use App\Http\Controllers\Api\ProjectPrixController;
use App\Http\Controllers\Api\ProjetController;
use App\Http\Controllers\Api\ProjetCollaborationController;
use App\Http\Controllers\Api\ProjetInvitationController;
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
    Route::post('forgot-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('reset-password', [\App\Http\Controllers\Api\ResetPasswordController::class, 'reset']);
});

Route::get('exports/{export}/download', [ExportController::class, 'download'])->name('exports.download');

// ── Project Invitations (User Inbox) ──
Route::middleware('auth:sanctum')->get('projet-invitations', [ProjetInvitationController::class, 'indexMyInvitations']);
Route::middleware('auth:sanctum')->delete('projet-invitations/{invitation}/reject', [ProjetInvitationController::class, 'reject']);
Route::middleware('auth:sanctum')->post('projet-invitations/{invitation}/accept', [ProjetInvitationController::class, 'accept']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);
    
    // User search for invitations
    Route::get('users/search', [AuthController::class, 'searchUsers']);

    Route::get('catalogue-articles/categories', [PrixCatalogueController::class, 'categories']);
    Route::apiResource('catalogue-articles', PrixCatalogueController::class)->parameters([
        'catalogue-articles' => 'prixCatalogue',
    ]);

    Route::get('notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);

    Route::apiResource('articles', ArticleController::class);
    Route::post('articles/{article}/variants', [ArticleController::class, 'addVariant']);
    Route::delete('variants/{variant}', [ArticleController::class, 'removeVariant']);
    Route::apiResource('projets', ProjetController::class);

    Route::prefix('projets/{projet}')->group(function () {
        
        // ── Collaboration / Invitations ──
        Route::get('collaborators', [ProjetCollaborationController::class, 'index']);
        Route::put('collaborators/{user}', [ProjetCollaborationController::class, 'update']);
        Route::delete('collaborators/{user}', [ProjetCollaborationController::class, 'destroy']);
        
        Route::get('invitations', [ProjetInvitationController::class, 'indexForProject']);
        Route::post('invitations', [ProjetInvitationController::class, 'store']);
        Route::delete('invitations/{invitation}', [ProjetInvitationController::class, 'destroy']);

        // ── Core Project Items ──
        Route::get('articles',                     [ProjectArticleController::class, 'index']);
        Route::post('articles',                    [ProjectArticleController::class, 'store']);
        Route::put('articles/{projectArticle}',    [ProjectArticleController::class, 'update']);
        Route::delete('articles/{projectArticle}', [ProjectArticleController::class, 'destroy']);
        Route::post('articles/sync',               [ProjectArticleController::class, 'sync']);

        Route::get('prix',                  [ProjectPrixController::class, 'index']);
        Route::post('prix',                 [ProjectPrixController::class, 'store']);
        Route::put('prix/{projectPrix}',    [ProjectPrixController::class, 'update']);
        Route::delete('prix/{projectPrix}', [ProjectPrixController::class, 'destroy']);
        Route::post('prix/sync',            [ProjectPrixController::class, 'sync']);

        // ── Exports ──
        Route::post('exports/cps',  [ExportController::class, 'exportCps']);
        Route::post('exports/rc',   [ExportController::class, 'exportRc']);
        Route::post('exports/brd',  [ExportController::class, 'exportBrd']);
        Route::get('exports',       [ExportController::class, 'listExports']);
    });

    Route::delete('exports/{export}', [ExportController::class, 'destroy']);
});
