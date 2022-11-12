<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\LogoController;
use App\Http\Controllers\Api\VoIPController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\WebFormController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\MailableController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TimezoneController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealBoardController;
use App\Http\Controllers\Api\HighlightController;
use App\Http\Controllers\Api\User\RoleController;
use App\Http\Controllers\Api\User\TeamController;
use App\Http\Controllers\Api\DealStatusController;
use App\Http\Controllers\Api\PipelineDisplayOrder;
use App\Http\Controllers\Api\ZapierHookController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\SystemToolsController;
use App\Http\Controllers\Api\TimelinePinController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OAuthAccountController;
use App\Http\Controllers\Api\PendingMediaController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\ActivityStateController;
use App\Http\Controllers\Api\PipelineStageController;
use App\Http\Controllers\Api\Twilio\TwilioController;
use App\Http\Controllers\Api\Updater\PatchController;
use App\Http\Controllers\Api\Resource\TableController;
use App\Http\Controllers\Api\Updater\UpdateController;
use App\Http\Controllers\Api\Resource\ActionController;
use App\Http\Controllers\Api\Resource\ExportController;
use App\Http\Controllers\Api\Resource\ImportController;
use App\Http\Controllers\Api\Resource\SearchController;
use App\Http\Controllers\Api\User\IssueTokenController;
use App\Http\Controllers\Api\User\PermissionController;
use App\Http\Controllers\Api\User\UserAvatarController;
use App\Http\Controllers\Api\Workflow\WorkflowTriggers;
use App\Http\Controllers\Api\Resource\CommentController;
use App\Http\Controllers\Api\Resource\TrashedController;
use App\Http\Controllers\Api\Twilio\TwilioAppController;
use App\Http\Controllers\Api\Calendar\CalendarController;
use App\Http\Controllers\Api\Resource\BillableController;
use App\Http\Controllers\Api\Resource\TimelineController;
use App\Http\Controllers\Api\Workflow\WorkflowController;
use App\Http\Controllers\Api\EmailAccount\EmailAccountSync;
use App\Http\Controllers\Api\User\UserInvitationController;
use App\Http\Controllers\Api\Resource\EmailSearchController;
use App\Http\Controllers\Api\Resource\ResourcefulController;
use App\Http\Controllers\Api\Resource\AssociationsController;
use App\Http\Controllers\Api\Resource\GlobalSearchController;
use App\Http\Controllers\Api\Resource\PlaceholdersController;
use App\Http\Controllers\Api\Calendar\CalendarOAuthController;
use App\Http\Controllers\Api\PredefinedMailTemplateController;
use App\Http\Controllers\Api\User\PersonalAccessTokenController;
use App\Http\Controllers\Api\EmailAccount\EmailAccountController;
use App\Http\Controllers\Api\Resource\AssociationsSyncController;
use App\Http\Controllers\Api\EmailAccount\SharedEmailAccountController;
use App\Http\Controllers\Api\EmailAccount\EmailAccountMessagesController;
use App\Http\Controllers\Api\EmailAccount\PersonalEmailAccountController;
use App\Http\Controllers\Api\EmailAccount\EmailAccountSyncStateController;
use App\Http\Controllers\Api\EmailAccount\EmailAccountPrimaryStateController;
use App\Http\Controllers\Api\EmailAccount\EmailAccountConnectionTestController;
use App\Http\Controllers\Api\Resource\FieldController as ResourceFieldController;
use App\Http\Controllers\Api\Resource\MediaController as ResourceMediaController;
use App\Http\Controllers\Api\Resource\FilterController as ResourceFilterController;

// Routes that does not require authentication must be added first on the top
// as seems like there is an issue when using the middleware auth:sanctum
// even if we add the routes at the bottom outside of the auth:sanctum middleware will thrown unauthenticated error

Route::post('/token', [IssueTokenController::class, 'store'])->middleware('guest');

Route::post('/voip/events', [VoIPController::class, 'events'])->name('voip.events');
Route::post('/voip/call', [VoIPController::class, 'newCall'])->name('voip.call');

Route::middleware('auth:sanctum')->group(function () {
    // Available timezones route
    Route::get('/timezones', [TimezoneController::class, 'handle']);

    Route::post('/zapier/hooks/{resourceName}/{action}', [ZapierHookController::class, 'store']);
    Route::delete('/zapier/hooks/{hookId}', [ZapierHookController::class, 'destroy']);

    Route::middleware('permission:use voip')->group(function () {
        Route::get('/voip/token', [VoIPController::class, 'newToken']);
    });

    // Personal access tokens routes
    Route::middleware('can:access-api')->group(function () {
        Route::get('/personal-access-tokens', [PersonalAccessTokenController::class, 'index']);
        Route::post('/personal-access-tokens', [PersonalAccessTokenController::class, 'store']);
        Route::delete('/personal-access-tokens/{token}', [PersonalAccessTokenController::class, 'destroy']);
    });

    // Notifications routes
    Route::apiResource('notifications', NotificationController::class)->except(['store', 'update']);
    Route::put('/notifications', [NotificationController::class, 'update']);


    // Media routes
    Route::post('/media/pending/{draftId}', [PendingMediaController::class, 'store']);
    Route::delete('/media/pending/{pendingMediaId}', [PendingMediaController::class, 'destroy']);

    // User profile routes
    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'password']);

    Route::get('/placeholders', [PlaceholdersController::class, 'index']);
    Route::post('/placeholders', [PlaceholdersController::class, 'parse']);

    // The {user} is not yet used.
    Route::post('/users/{user}/avatar', [UserAvatarController::class, 'store']);
    Route::delete('/users/{user}/avatar', [UserAvatarController::class, 'delete']);

    // Resource fields route
    Route::get('/fields/{group}/{view}', [FieldController::class, 'index']);

    // Email accounts routes
    Route::prefix('mail/accounts')->group(function () {
        // Email accounts management
        Route::get('{account}/sync', EmailAccountSync::class);
        Route::get('unread', [EmailAccountController::class, 'unread']);

        // The GET route for all shared accounts
        Route::get('shared', SharedEmailAccountController::class)->middleware('permission:access shared inbox');

        // The GET route for all logged in user personal mail accounts
        Route::get('personal', PersonalEmailAccountController::class);

        // Test connection route
        Route::post('connection', [EmailAccountConnectionTestController::class, 'handle']);

        Route::put('{account}/primary', [EmailAccountPrimaryStateController::class, 'update']);
        Route::delete('primary', [EmailAccountPrimaryStateController::class, 'destroy']);
        Route::post('{account}/sync/enable', [EmailAccountSyncStateController::class, 'enable']);
        Route::post('{account}/sync/disable', [EmailAccountSyncStateController::class, 'disable']);
    });

    Route::apiResource('/mail/accounts', EmailAccountController::class);

    // OAuth accounts controller
    Route::apiResource('/oauth/accounts', OAuthAccountController::class, ['as' => 'oauth'])
        ->except(['store', 'update']);

    Route::get('highlights', HighlightController::class);

    Route::apiResource('/teams', TeamController::class)->only(['show', 'index']);

    Route::middleware('app.superadmin')->group(function () {
        Route::post('/users/invite', [UserInvitationController::class, 'handle']);

        Route::apiResource('/teams', TeamController::class)->except(['show', 'index']);

        Route::prefix('system')->group(function () {
            Route::get('logs', [SystemController::class, 'logs']);
            Route::get('info', [SystemController::class, 'info']);
            Route::post('info', [SystemController::class, 'downloadInfo']);
        });

        Route::get('currencies', CurrencyController::class);

        // Twilio integration routes
        Route::prefix('twilio')->group(function () {
            Route::delete('/', [TwilioController::class, 'destroy']);
            Route::get('numbers', [TwilioController::class, 'index']);

            Route::get('app/{id}', [TwilioAppController::class, 'show']);
            Route::post('app', [TwilioAppController::class, 'create']);
            Route::put('app/{id}', [TwilioAppController::class, 'update']);
            Route::delete('app/{sid}', [TwilioAppController::class, 'destroy']);
        });

        // Translation management
        Route::prefix('translation')->group(function () {
            Route::post('/', [TranslationController::class, 'store']);
            Route::get('/{locale}', [TranslationController::class, 'index']);
            Route::put('/{locale}/{group}', [TranslationController::class, 'update']);
        });

        // Tools
        Route::prefix('tools')->group(function () {
            Route::get('i18n-generate', [SystemToolsController::class, 'i18n']);
            Route::get('storage-link', [SystemToolsController::class, 'storageLink']);
            Route::get('clear-cache', [SystemToolsController::class, 'clearCache']);
            Route::get('migrate', [SystemToolsController::class, 'migrate']);
            Route::get('optimize', [SystemToolsController::class, 'optimize']);
            Route::get('seed-mailables', [SystemToolsController::class, 'seedMailableTemplates']);
        });

        // Logo
        Route::post('/logo/{type}', [LogoController::class, 'store']);
        Route::delete('/logo/{type}', [LogoController::class, 'destroy']);

        // General Settings
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::post('/settings', [SettingsController::class, 'save']);

        // Application update management
        Route::get('/patches', [PatchController::class, 'index']);
        Route::post('/patches/{token}/{purchase_key?}', [PatchController::class, 'apply']);

        Route::get('/update', [UpdateController::class, 'index']);
        Route::post('/update/{purchase_key?}', [UpdateController::class, 'update']);

        // Custom fields routes
        Route::apiResource('/custom-fields', CustomFieldController::class);

        // Settings intended fields
        Route::prefix('fields/settings')->group(function () {
            Route::post('{group}/{view}', [FieldController::class, 'update']);
            Route::get('bulk/{view}', [FieldController::class, 'bulkSettings']);
            Route::get('{group}/{view}', [FieldController::class, 'settings']);
            Route::delete('{group}/{view}/reset', [FieldController::class, 'destroy']);
        });

        // Workflows
        Route::get('/workflows/triggers', WorkflowTriggers::class);
        Route::apiResource('workflows', WorkflowController::class);

        // Settings roles and permissions
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::apiResource('roles', RoleController::class);

        // Mailable templates
        Route::prefix('mailables')->group(function () {
            Route::get('/', [MailableController::class, 'index']);
            Route::get('{locale}/locale', [MailableController::class, 'forLocale']);
            Route::get('{template}', [MailableController::class, 'show']);
            Route::put('{template}', [MailableController::class, 'update']);
        });

        // Web forms routes
        Route::apiResource('/forms', WebFormController::class);
    });

    Route::prefix('emails')->group(function () {
        Route::post('{message}/read', [EmailAccountMessagesController::class, 'read']);
        Route::post('{message}/unread', [EmailAccountMessagesController::class, 'unread']);
        Route::delete('{message}', [EmailAccountMessagesController::class, 'destroy']);
        // reply method is used to check in MessageRequest
        Route::post('{message}/reply', [EmailAccountMessagesController::class, 'reply']);
        Route::post('{message}/forward', [EmailAccountMessagesController::class, 'forward']);
    });

    Route::prefix('inbox')->group(function () {
        Route::get('emails/folders/{folder_id}/{message}', [EmailAccountMessagesController::class, 'show']);
        Route::post('emails/{account_id}', [EmailAccountMessagesController::class, 'create']);
        Route::get('emails/{account_id}/{folder_id}', [EmailAccountMessagesController::class, 'index']);
    });

    // Calendar routes
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index']);
        Route::get('/account', [CalendarOAuthController::class, 'index']);
        Route::post('/account', [CalendarOAuthController::class, 'save']);
        Route::delete('/account', [CalendarOAuthController::class, 'destroy']);
    });

    Route::get('/calendars/{account}', [CalendarOAuthController::class, 'calendars']);

    // Mail templates management
    Route::apiResource('mails/templates', PredefinedMailTemplateController::class);

    // Activity pins management
    Route::post('/timeline/pin', [TimelinePinController::class, 'store']);
    Route::post('/timeline/unpin', [TimelinePinController::class, 'destroy']);

    // Available countries route
    Route::get('/countries', [CountryController::class, 'handle']);

    Route::put('/deals/{id}/status/{status}', [DealStatusController::class, 'handle']);

    // Deals board management
    Route::prefix('deals/board')->group(function () {
        Route::get('{pipeline}', [DealBoardController::class, 'board']);
        Route::post('{pipeline}', [DealBoardController::class, 'update']);
        Route::get('{pipeline}/summary', [DealBoardController::class, 'summary']);
        Route::post('{pipeline}/sort', [DealBoardController::class, 'saveSort']);
    });

    // Deal routes
    Route::get('/pipelines/{id}/stages', [PipelineStageController::class, 'index']);
    Route::post('/pipelines/order', PipelineDisplayOrder::class);

    // Billable management
    Route::post('/{resource}/{resourceId}/billable', [BillableController::class, 'handle']);

    // Filters management
    Route::get('/{resource}/rules', [ResourceFilterController::class, 'rules']);
    Route::get('/{resource}/filters', [ResourceFilterController::class, 'index']);

    Route::prefix('filters')->group(function () {
        Route::put('{id}/{view}/default', [FilterController::class, 'markAsDefault']);
        Route::delete('{id}/{view}/default', [FilterController::class, 'unmarkAsDefault']);
        Route::get('{identifier}', [FilterController::class, 'index']);
        Route::post('/', [FilterController::class, 'store']);
        Route::put('{id}', [FilterController::class, 'update']);
        Route::delete('{id}', [FilterController::class, 'destroy']);
    });

    // Activity routes
    Route::get('/activities/{activity}/ics', [ActivityController::class, 'downloadICS']);
    Route::post('/activities/{activity}/complete', [ActivityStateController::class, 'complete']);
    Route::post('/activities/{activity}/incomplete', [ActivityStateController::class, 'incomplete']);

    // Comments management
    Route::get('{resource}/{resourceId}/comments', [CommentController::class, 'index']);
    Route::post('{resource}/{resourceId}/comments', [CommentController::class, 'store']);
    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Resource import handling
    Route::get('/{resource}/import', [ImportController::class, 'index']);
    Route::post('/{resource}/import/upload', [ImportController::class, 'upload']);
    Route::post('/{resource}/import/{id}', [ImportController::class, 'handle']);
    Route::delete('/{resource}/import/{id}', [ImportController::class, 'destroy']);
    Route::get('/{resource}/import/sample', [ImportController::class, 'sample']);

    Route::post('/{resource}/export', [ExportController::class, 'handle']);

    // Dashboard controller
    Route::apiResource('dashboards', DashboardController::class);

    // Cards controller
    Route::get('/cards', [CardController::class, 'forDashboards']);
    Route::get('/cards/{card}', [CardController::class, 'show'])->name('cards.show');
    Route::get('/{resource}/cards/', [CardController::class, 'index']);

    // Searches
    Route::get('/search', [GlobalSearchController::class, 'handle']);
    Route::get('/search/email-address', [EmailSearchController::class, 'handle']);
    Route::get('/{resource}/search', [SearchController::class, 'handle']);

    // Resource associations routes
    Route::put('associations/{resource}/{resourceId}', [AssociationsSyncController::class, 'attach']);
    Route::post('associations/{resource}/{resourceId}', [AssociationsSyncController::class, 'sync']);
    Route::delete('associations/{resource}/{resourceId}', [AssociationsSyncController::class, 'detach']);

    Route::post('{resource}/{resourceId}/media', [ResourceMediaController::class, 'store']);
    Route::delete('{resource}/{resourceId}/media/{media}', [ResourceMediaController::class, 'destroy']);

    // Resource trash
    Route::get('/trashed/{resource}/search', [TrashedController::class, 'search']);
    Route::post('/trashed/{resource}/{resourceId}', [TrashedController::class, 'restore']);
    Route::get('/trashed/{resource}', [TrashedController::class, 'index']);
    Route::get('/trashed/{resource}/{resourceId}', [TrashedController::class, 'show']);
    Route::delete('/trashed/{resource}/{resourceId}', [TrashedController::class, 'destroy']);

    // Resource management
    Route::get('/{resource}/table', [TableController::class, 'index']);
    Route::get('/{resource}/table/settings', [TableController::class, 'settings']);
    Route::post('/{resource}/table/settings', [TableController::class, 'customize']);

    Route::post('/{resource}/actions/{action}/run', [ActionController::class, 'handle']);

    Route::get('/{resource}/{resourceId}/update-fields', [ResourceFieldController::class, 'update']);
    Route::get('/{resource}/{resourceId}/detail-fields', [ResourceFieldController::class, 'detail']);
    Route::get('/{resource}/{resourceId}/timeline', [TimelineController::class, 'index']);
    Route::get('/{resource}/{resourceId}/{associated}', AssociationsController::class);
    Route::get('/{resource}/create-fields', [ResourceFieldController::class, 'create']);
    Route::get('/{resource}', [ResourcefulController::class, 'index']);
    Route::get('/{resource}/{resourceId}', [ResourcefulController::class, 'show']);
    Route::post('/{resource}', [ResourcefulController::class, 'store']);
    Route::put('/{resource}/{resourceId}', [ResourcefulController::class, 'update']);
    Route::delete('/{resource}/{resourceId}', [ResourcefulController::class, 'destroy']);
});
