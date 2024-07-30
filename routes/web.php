<?php

use Illuminate\Support\Facades\Route;

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

//Frontend Routes
Route::match(['get', 'post'], '/', [App\Http\Controllers\FrontendController::class, 'index']);
Route::match(['get', 'post'], '/process-campaign', [App\Http\Controllers\FrontendController::class, 'buildTemplateChatMessage']);
Route::get('/language/{locale}', [App\Http\Controllers\FrontendController::class, 'changeLanguage']);

//File Route
Route::get('media/{filename}', [App\Http\Controllers\FileController::class, 'show'])->where('filename', '.*');

//Authentication Routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::get('/social-login/{type?}', [App\Http\Controllers\AuthController::class, 'socialLogin']);
Route::get('/google/callback', [App\Http\Controllers\AuthController::class, 'googleCallback'])->name('google.callback');
Route::get('/facebook/callback', [App\Http\Controllers\AuthController::class, 'handleFacebookCallback']);

Route::get('/signup', [App\Http\Controllers\AuthController::class, 'showRegistrationForm']);
Route::post('/signup', [App\Http\Controllers\AuthController::class, 'handleRegistration']);

Route::get('/invite/{identifier}', [App\Http\Controllers\AuthController::class, 'viewInvite']);
Route::post('/invite/{identifier}', [App\Http\Controllers\AuthController::class, 'invite']);
Route::get('/forgot-password', [App\Http\Controllers\AuthController::class, 'showForgotForm']);
Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'createPasswordResetToken']);
Route::get('/reset-password', [App\Http\Controllers\AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::match(['get', 'post'], '/whatsapp', [App\Http\Controllers\User\WhatsappController::class, 'sendMessage']);

//Webhook
Route::match(['get', 'post'], '/webhook/whatsapp/{identifier?}', [App\Http\Controllers\WebhookController::class, 'handle']);
Route::match(['get', 'post'], '/webhook/{processor}', [App\Http\Controllers\WebhookController::class, 'processWebhook']);
Route::match(['get', 'post'], '/payment/{processor}', [App\Http\Controllers\PaymentController::class, 'processPayment']);

Route::get('/campaign-send', [App\Http\Controllers\FrontendController::class, 'sendCampaign']);
Route::get('/migrate-upgrade', [App\Http\Controllers\FrontendController::class, 'migrate']);

Route::middleware(['auth:user'])->group(function () {
    Route::get('/select-organization', [App\Http\Controllers\User\OrganizationController::class, 'index'])->name('user.organization.index');
    Route::post('/organization', [App\Http\Controllers\User\OrganizationController::class, 'store'])->name('user.organization.store');

    Route::group(['middleware' => 'check.organization'], function () {
        //User Panel Routes
        Route::match(['get', 'post'], '/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])->name('dashboard');

        Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update']);
        Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword']);
        Route::put('/profile/organization', [App\Http\Controllers\ProfileController::class, 'updateOrganization']);

        Route::group(['middleware' => 'check.client.role'], function () {
            Route::delete('dismiss-notification/{type}', [App\Http\Controllers\User\DashboardController::class, 'dismissNotification'])->name('dashboard.team.notification.dismiss');
            Route::match(['get', 'post'], '/billing', [App\Http\Controllers\User\BillingController::class, 'index'])->name('user.billing.index');
            Route::post('/pay', [App\Http\Controllers\User\BillingController::class, 'pay'])->name('user.billing.pay');
            Route::resource('subscription', App\Http\Controllers\User\SubscriptionController::class);
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
            Route::delete('/contacts/{uuid}', [App\Http\Controllers\User\ContactController::class, 'delete']);

            Route::get('/contact-groups', [App\Http\Controllers\User\ContactGroupController::class, 'index']);
            Route::post('/contact-groups', [App\Http\Controllers\User\ContactGroupController::class, 'store']);
            Route::post('/contact-groups/{uuid}', [App\Http\Controllers\User\ContactGroupController::class, 'update']);
            Route::delete('/contact-groups/{uuid}', [App\Http\Controllers\User\ContactGroupController::class, 'delete']);

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

//Admin Panel Routes
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('organizations', App\Http\Controllers\Admin\OrganizationController::class);
    Route::resource('blog/posts', App\Http\Controllers\Admin\BlogController::class);
    Route::resource('blog/categories', App\Http\Controllers\Admin\BlogCategoryController::class);
    Route::resource('blog/authors', App\Http\Controllers\Admin\BlogAuthorController::class);
    Route::resource('blog/tags', App\Http\Controllers\Admin\BlogTagController::class);
    Route::resource('tax-rates', App\Http\Controllers\Admin\TaxController::class);
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);
    Route::resource('faqs', App\Http\Controllers\Admin\FaqController::class);
    Route::resource('testimonials', App\Http\Controllers\Admin\TestimonialController::class);
    Route::resource('plans', App\Http\Controllers\Admin\SubscriptionPlanController::class);
    Route::resource('team/users', App\Http\Controllers\Admin\TeamController::class);
    Route::resource('team/roles', App\Http\Controllers\Admin\RoleController::class);
    Route::resource('billing', App\Http\Controllers\Admin\BillingController::class);
    Route::resource('addons', App\Http\Controllers\Admin\AddonController::class);
    Route::post('addons/install', [App\Http\Controllers\Admin\AddonController::class, 'install']);
    Route::post('/addons/setup/google-recaptcha', [App\Http\Controllers\Admin\AddonController::class, 'store']);
    Route::post('/addons/setup/google-analytics', [App\Http\Controllers\Admin\AddonController::class, 'store']);
    Route::post('/addons/setup/google-maps', [App\Http\Controllers\Admin\AddonController::class, 'store']);
    Route::resource('payment-gateways', App\Http\Controllers\Admin\PaymentGatewayController::class)->only(['index', 'show', 'update']);
    Route::get('/languages/{language}/export', [App\Http\Controllers\Admin\LanguageController::class, 'export']);
    Route::post('/languages/{language}/import', [App\Http\Controllers\Admin\LanguageController::class, 'import']);
    Route::get('/languages/{language}/translations', [App\Http\Controllers\Admin\LanguageController::class, 'translations']);
    Route::resource('languages', App\Http\Controllers\Admin\LanguageController::class);
    Route::post('/translations/{languageCode}/{key}', [App\Http\Controllers\Admin\LanguageController::class, 'updateTranslation']);

    Route::get('/pages', [App\Http\Controllers\Admin\PageController::class, 'index']);

    Route::get('/users/{uuid}/organizations', [App\Http\Controllers\Admin\CustomerController::class, 'userOrganizations']);
    Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionController::class, 'index']);
    Route::get('/payment-logs', [App\Http\Controllers\Admin\PaymentController::class, 'index']);

    Route::get('/support/{uuid?}', [App\Http\Controllers\Admin\TicketController::class, 'index'])->name('tickets');
    Route::post('/support', [App\Http\Controllers\Admin\TicketController::class, 'store']);
    Route::post('/support/{uuid}/comment', [App\Http\Controllers\Admin\TicketController::class, 'comment']);
    Route::post('/support/{uuid}/status', [App\Http\Controllers\Admin\TicketController::class, 'changeStatus']);
    Route::post('/support/{uuid}/priority', [App\Http\Controllers\Admin\TicketController::class, 'changePriority']);
    Route::post('/support/{uuid}/assign', [App\Http\Controllers\Admin\TicketController::class, 'assign']);

    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index']);
    Route::match(['get', 'post'], '/settings/general', [App\Http\Controllers\Admin\SettingController::class, 'general']);
    Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update']);
    Route::get('/settings/smtp', [App\Http\Controllers\Admin\SettingController::class, 'email']);
    Route::get('/settings/broadcast-drivers', [App\Http\Controllers\Admin\SettingController::class, 'broadcast_driver']);
    Route::match(['get', 'post'], '/settings/timezone', [App\Http\Controllers\Admin\SettingController::class, 'timezone']);
    Route::get('/settings/email-templates', [App\Http\Controllers\Admin\EmailTemplateController::class, 'index']);
    Route::get('/settings/email-template/{id}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'show']);
    Route::put('/settings/email-template/{id}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'update']);
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
