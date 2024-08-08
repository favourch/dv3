<?php

use App\Models\Language;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Installer routes
Route::get('/install/{step?}', [App\Http\Controllers\InstallerController::class, 'index'])->name('install');
Route::post('/install/configure-database', [App\Http\Controllers\InstallerController::class, 'configureDatabase']);
Route::post('/install/configure-company', [App\Http\Controllers\InstallerController::class, 'configureCompany']);
Route::post('/install/migrate', [App\Http\Controllers\InstallerController::class, 'runMigrations']);
Route::get('/update', [App\Http\Controllers\InstallerController::class, 'update'])->name('install.update');
Route::post('/update', [App\Http\Controllers\InstallerController::class, 'runUpdate']);



    // If using web.php, ensure it's not in the API group

    Route::get('/admin/dashboard/data', [DashboardController::class, 'getDashboardData']);


Route::get('/current-locale', function () {
    return response()->json(['locale' => app()->getLocale()]);
});

Route::get('/locales', function () {
    $locales = Language::all()->pluck('code');
    return response()->json($locales);
});

Route::get('/translations/{locale}', function ($locale) {
    $path = base_path("lang/{$locale}.json");
    if (File::exists($path)) {
        $translations = File::get($path);
        return response()->json(json_decode($translations, true));
    } else {
        return response()->json(['error' => 'Locale not found'], 404);
    }
});

//Frontend Routes
Route::match(['get', 'post'], '/', [App\Http\Controllers\FrontendController::class, 'index']);
Route::match(['get', 'post'], '/pages/{slug}', [App\Http\Controllers\FrontendController::class, 'pages']);
Route::match(['get', 'post'], '/privacy', [App\Http\Controllers\FrontendController::class, 'privacy']);
Route::match(['get', 'post'], '/terms-of-service', [App\Http\Controllers\FrontendController::class, 'termsOfService']);
Route::match(['get', 'post'], '/process-campaign', [App\Http\Controllers\FrontendController::class, 'buildTemplateChatMessage']);
Route::get('/language/{locale}', [App\Http\Controllers\FrontendController::class, 'changeLanguage']);

//File Route
Route::get('media/{filename}', [App\Http\Controllers\FileController::class, 'show'])->where('filename', '.*');

//Invite Routes
Route::get('/invite/{identifier}', [App\Http\Controllers\AuthController::class, 'viewInvite']);
Route::post('/invite/{identifier}', [App\Http\Controllers\AuthController::class, 'invite']);

Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');


Route::get('/campaign-send', [App\Http\Controllers\FrontendController::class, 'sendCampaign']);
Route::get('/migrate-upgrade', [App\Http\Controllers\FrontendController::class, 'migrate']);

Route::middleware(['guest', 'redirectIfAuthenticated:user,admin'])->group(function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login');
    Route::get('/signup', [App\Http\Controllers\AuthController::class, 'showRegistrationForm']);
    Route::post('/signup', [App\Http\Controllers\AuthController::class, 'handleRegistration']);
    Route::get('/forgot-password', [App\Http\Controllers\AuthController::class, 'showForgotForm']);
    Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'createPasswordResetToken']);
    Route::get('/reset-password', [App\Http\Controllers\AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:user'])->group(function () {
    Route::get('/email/verify', [App\Http\Controllers\AuthController::class, 'verifyEmail'])->middleware('auth')->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', [App\Http\Controllers\AuthController::class, 'sendEmailVerification'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::group(['middleware' => ['check.email.verification']], function () {
        Route::get('/select-organization', [App\Http\Controllers\User\OrganizationController::class, 'index'])->name('user.organization.index');
        Route::post('/organization', [App\Http\Controllers\User\OrganizationController::class, 'store'])->name('user.organization.store');

        Route::group(['middleware' => ['check.organization']], function () {
            //User Panel Routes
            Route::match(['get', 'post'], '/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])->name('dashboard');

            Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update']);
            Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword']);
            Route::put('/profile/organization', [App\Http\Controllers\ProfileController::class, 'updateOrganization']);

            Route::group(['middleware' => 'check.client.role'], function () {
                Route::delete('dismiss-notification/{type}', [App\Http\Controllers\User\DashboardController::class, 'dismissNotification'])->name('dashboard.team.notification.dismiss');
               
            });

            Route::group(['middleware' => 'check.subscription'], function () {
                Route::get('/chats/{uuid?}', [App\Http\Controllers\User\ChatController::class, 'index']);
                Route::get('/chats/{id}/media', [App\Http\Controllers\User\ChatController::class, 'getMedia']);
                Route::post('/chats', [App\Http\Controllers\User\ChatController::class, 'sendMessage']);
                Route::delete('/chats/{uuid}', [App\Http\Controllers\User\ChatController::class, 'deleteChats']);
                Route::get('/chat/send', [App\Http\Controllers\User\ChatController::class, 'sendMessage']);
                Route::get('/chat/test/{id}', [App\Http\Controllers\User\ChatController::class, 'sendAutoReply']);
                Route::post('/chats/update-sort-direction', [App\Http\Controllers\User\ChatController::class, 'updateChatSortDirection']);

                Route::get('/tickets/{status}', [App\Http\Controllers\User\ChatTicketController::class, 'index']);
                Route::put('/tickets/{uuid}/update', [App\Http\Controllers\User\ChatTicketController::class, 'update']);
                Route::put('/tickets/{uuid}/assign', [App\Http\Controllers\User\ChatTicketController::class, 'assign']);

                Route::get('/contacts/{uuid?}', [App\Http\Controllers\User\ContactController::class, 'index'])->name('contacts');
                Route::post('/contacts', [App\Http\Controllers\User\ContactController::class, 'store']);
                Route::post('/contacts/import', [App\Http\Controllers\User\ContactController::class, 'import']);
                Route::post('/contacts/{uuid}', [App\Http\Controllers\User\ContactController::class, 'update']);
                Route::put('/contacts/favorite/{uuid}', [App\Http\Controllers\User\ContactController::class, 'favorite']);
                Route::delete('/contacts', [App\Http\Controllers\User\ContactController::class, 'delete']);

                Route::get('/contact-groups/{uuid?}', [App\Http\Controllers\User\ContactGroupController::class, 'index']);
                Route::post('/contact-groups', [App\Http\Controllers\User\ContactGroupController::class, 'store']);
                Route::post('/contact-groups/import', [App\Http\Controllers\User\ContactGroupController::class, 'import']);
                Route::post('/contact-groups/{uuid}', [App\Http\Controllers\User\ContactGroupController::class, 'update']);
                Route::delete('/contact-groups', [App\Http\Controllers\User\ContactGroupController::class, 'delete']);

                Route::get('/campaigns/{uuid?}', [App\Http\Controllers\User\CampaignController::class, 'index'])->name('campaigns');
                Route::post('/campaigns', [App\Http\Controllers\User\CampaignController::class, 'store']);
                Route::get('/campaigns/export/{uuid?}', [App\Http\Controllers\User\CampaignController::class, 'export']);
                Route::delete('/campaigns/{uuid?}', [App\Http\Controllers\User\CampaignController::class, 'delete']);

                Route::match(['get', 'post'], '/templates/create', [App\Http\Controllers\User\TemplateController::class, 'create']);
                Route::get('/templates/{uuid?}', [App\Http\Controllers\User\TemplateController::class, 'index']);
                Route::post('/templates', [App\Http\Controllers\User\TemplateController::class, 'store']);
                Route::post('/templates/{uuid}', [App\Http\Controllers\User\TemplateController::class, 'update']);
                Route::delete('/templates/{uuid}', [App\Http\Controllers\User\TemplateController::class, 'delete']);

                Route::get('/canned-replies', [App\Http\Controllers\User\CannedReplyController::class, 'index'])->name('cannedReply');
                Route::get('/canned-replies/create', [App\Http\Controllers\User\CannedReplyController::class, 'create'])->name('cannedReply.create');
                Route::post('/canned-replies', [App\Http\Controllers\User\CannedReplyController::class, 'store'])->name('cannedReply.store');
                Route::get('/canned-replies/{uuid}/edit', [App\Http\Controllers\User\CannedReplyController::class, 'edit'])->name('cannedReply.edit');
                Route::put('/canned-replies/{uuid}', [App\Http\Controllers\User\CannedReplyController::class, 'update'])->name('cannedReply.update');
                Route::delete('/canned-replies/{uuid}', [App\Http\Controllers\User\CannedReplyController::class, 'delete'])->name('cannedReply.destroy');

                Route::get('/support/{uuid?}', [App\Http\Controllers\User\TicketController::class, 'index'])->name('support');
                Route::post('/support', [App\Http\Controllers\User\TicketController::class, 'store']);
                Route::post('/support/{uuid}/comment', [App\Http\Controllers\User\TicketController::class, 'comment']);
                Route::post('/support/{uuid}/status', [App\Http\Controllers\User\TicketController::class, 'changeStatus']);
                Route::post('/support/{uuid}/priority', [App\Http\Controllers\User\TicketController::class, 'changePriority']);

                Route::match(['get', 'post'], '/messages', [App\Http\Controllers\User\MessageController::class, 'index']);
                Route::match(['get', 'post'], '/message-templates', [App\Http\Controllers\User\TemplateController::class, 'index']);
                Route::match(['get', 'post'], '/instances', [App\Http\Controllers\User\InstanceController::class, 'index']);

                Route::group(['middleware' => 'check.client.role'], function () {
                    Route::get('/settings', [App\Http\Controllers\User\SettingController::class, 'index']);
                    Route::get('/settings/m', [App\Http\Controllers\User\SettingController::class, 'mobileView']);

                    Route::get('/settings/whatsapp', [App\Http\Controllers\User\SettingController::class, 'viewWhatsappSettings']);
                    Route::get('/settings/whatsapp/refresh', [App\Http\Controllers\User\SettingController::class, 'refreshWhatsappData']);
                    Route::post('/settings/whatsapp/token', [App\Http\Controllers\User\SettingController::class, 'updateToken']);
                    Route::post('/settings/whatsapp', [App\Http\Controllers\User\SettingController::class, 'storeWhatsappSettings']);
                    Route::post('/settings/whatsapp/business-profile', [App\Http\Controllers\User\SettingController::class, 'whatsappBusinessProfileUpdate']);
                    Route::delete('/settings/whatsapp/business-profile', [App\Http\Controllers\User\SettingController::class, 'deleteWhatsappIntegration']);
                    Route::match(['get', 'post'], '/settings/contacts', [App\Http\Controllers\User\SettingController::class, 'contacts']);
                    Route::match(['get', 'post'], '/settings/tickets', [App\Http\Controllers\User\SettingController::class, 'tickets']);
                    Route::resource('contact-fields', App\Http\Controllers\User\ContactFieldController::class);

                    Route::get('/team', [App\Http\Controllers\User\TeamController::class, 'index'])->name('team');
                    Route::post('/team/invite', [App\Http\Controllers\User\TeamController::class, 'invite'])->name('team.store');
                    Route::put('/team/{uuid}', [App\Http\Controllers\User\TeamController::class, 'update'])->name('team.update');
                    Route::delete('/team/{uuid}', [App\Http\Controllers\User\TeamController::class, 'delete'])->name('team.destroy');

                    Route::get('/developer', [App\Http\Controllers\User\DeveloperController::class, 'index']);
                    Route::post('/developer', [App\Http\Controllers\User\DeveloperController::class, 'store']);
                    Route::delete('/developer/{uuid}', [App\Http\Controllers\User\DeveloperController::class, 'delete']);
                });

                Route::get('/whatsapp/message', [App\Http\Controllers\User\WhatsappController::class, 'sendMessage']);
                Route::resource('notes', App\Http\Controllers\User\ChatNoteController::class);
            });
        });
    });
});

//Admin Panel Routes
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('/languages/{language}/export', [App\Http\Controllers\Admin\LanguageController::class, 'export']);
    Route::post('/languages/{language}/import', [App\Http\Controllers\Admin\LanguageController::class, 'import']);
    Route::get('/languages/{language}/translations', [App\Http\Controllers\Admin\LanguageController::class, 'translations']);
    Route::get('/languages/{language}/default', [App\Http\Controllers\Admin\LanguageController::class, 'setDefault']);
    Route::resource('languages', App\Http\Controllers\Admin\LanguageController::class);
    Route::post('/translations/{languageCode}/{key}', [App\Http\Controllers\Admin\LanguageController::class, 'updateTranslation']);

    Route::get('/pages', [App\Http\Controllers\Admin\PageController::class, 'index']);

    
    Route::match(['get', 'post'], '/settings/billing', [App\Http\Controllers\Admin\SettingController::class, 'billing']);
    Route::get('/settings/storage', [App\Http\Controllers\Admin\SettingController::class, 'storage']);
    Route::get('/settings/socials', [App\Http\Controllers\Admin\SettingController::class, 'socials']);
    Route::get('/settings/subscription', [App\Http\Controllers\Admin\SettingController::class, 'subscription']);

    Route::get('/user-logs/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index']);
    Route::get('/user-logs/emails', [App\Http\Controllers\Admin\EmailLogController::class, 'index']);

    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update']);
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword']);
});

Route::prefix('api/')->group(function() {
    Route::get('chats/{lastmessagetime}', 'ChatController@chatlist');
    Route::get('chat/{contact}', 'ChatController@chatmessages');
    Route::post('send/{contact}', 'ChatController@sendMessageToContact');
    Route::post('sendimage/{contact}', 'ChatController@sendImageMessageToContact');
    Route::post('sendfile/{contact}', 'ChatController@sendDocumentMessageToContact');
});
