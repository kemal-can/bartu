<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrivacyPolicy;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\ScriptController;
use App\Http\Controllers\MigrationRequired;
use App\Http\Controllers\WebFormController;
use App\Http\Controllers\MediaViewController;
use App\Http\Controllers\FilePermissionsError;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\RequirementsController;
use App\Http\Controllers\GoogleWebhookController;
use App\Http\Controllers\OAuthCalendarController;
use App\Http\Controllers\UpdateDownloadController;
use App\Http\Controllers\OAuthEmailAccountController;
use App\Http\Controllers\UserInvitationAcceptController;
use App\Http\Controllers\OutlookCalendarWebhookController;
use Illuminate\Http\Middleware\CheckResponseForModifications;

Auth::routes([
    'register' => false,
    'verify'   => false,
    'confirm'  => false,
]);

Route::get('/scripts/{script}', [ScriptController::class, 'show'])->middleware(CheckResponseForModifications::class);
Route::get('/styles/{style}', [StyleController::class, 'show'])->middleware(CheckResponseForModifications::class);

Route::get('privacy-policy', PrivacyPolicy::class);

Route::middleware(['auth', 'app.superadmin'])->group(function () {
    // Errors routes
    Route::get('/errors/permissions', FilePermissionsError::class);
    Route::get('/errors/migration', MigrationRequired::class);
    Route::get('/requirements', [RequirementsController::class, 'show']);
    Route::post('/requirements', [RequirementsController::class, 'confirm']);

    Route::get('/patches/{token}/{purchase_key?}', [UpdateDownloadController::class, 'downloadPatch']);
});

Route::post('/webhook/outlook-calendar', [OutlookCalendarWebhookController::class, 'handle'])
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::post('/webhook/google', [GoogleWebhookController::class, 'handle'])
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/invitation/{token}', [UserInvitationAcceptController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}', [UserInvitationAcceptController::class, 'accept']);

Route::get('/forms/f/{uuid}', [WebFormController::class, 'show'])->name('webform.view');
Route::post('/forms/f/{uuid}', [WebFormController::class, 'store'])->name('webform.process')
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/media/{token}', [MediaViewController::class, 'show']);
Route::get('/media/{token}/download', [MediaViewController::class, 'download']);
Route::get('/media/{token}/preview', [MediaViewController::class, 'preview']);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/mail/accounts/{type}/{provider}/connect', [OAuthEmailAccountController::class, 'connect']);
    Route::get('/calendar/sync/{provider}/connect', [OAuthCalendarController::class, 'connect']);

    Route::get('/{providerName}/connect', [OAuthController::class, 'connect'])->where('providerName', 'microsoft|google');
    Route::get('/{providerName}/callback', [OAuthController::class, 'callback'])->where('providerName', 'microsoft|google');
});

// This route must be defined last
Route::get('/{any}', ApplicationController::class)
    ->where('any', '.*')
    ->middleware(['auth']);
