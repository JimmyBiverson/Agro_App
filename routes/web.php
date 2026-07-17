<?php

use App\Http\Controllers\WebController;
use App\Http\Middleware\AuthenticateWeb;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('web.dashboard'));

Route::get('/login', [WebController::class, 'showLogin'])->name('web.login');
Route::post('/login', [WebController::class, 'login'])->name('web.login.submit');

Route::middleware(AuthenticateWeb::class)->group(function () {
    Route::post('/logout', [WebController::class, 'logout'])->name('web.logout');
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('web.dashboard');

    // Profile
    Route::get('/profile', [WebController::class, 'profile'])->name('web.profile');
    Route::post('/profile', [WebController::class, 'profileUpdate'])->name('web.profile.update');
    Route::post('/profile/password', [WebController::class, 'profilePassword'])->name('web.profile.password');
    Route::post('/profile/avatar', [WebController::class, 'profileAvatar'])->name('web.profile.avatar');

    // Admin
    Route::get('/admin/franchises', [WebController::class, 'adminFranchises'])->name('web.admin.franchises');
    Route::get('/admin/users', [WebController::class, 'adminUsers'])->name('web.admin.users');
    Route::get('/admin/products', [WebController::class, 'adminProducts'])->name('web.admin.products');
    Route::get('/admin/categories', [WebController::class, 'adminCategories'])->name('web.admin.categories');
    Route::get('/admin/orders', [WebController::class, 'adminOrders'])->name('web.admin.orders');
    Route::get('/admin/payments', [WebController::class, 'adminPayments'])->name('web.admin.payments');
    Route::get('/admin/reports', [WebController::class, 'adminReports'])->name('web.admin.reports');
    Route::get('/admin/audit', [WebController::class, 'adminAudit'])->name('web.admin.audit');
    Route::get('/admin/news', [WebController::class, 'adminNews'])->name('web.admin.news');
    Route::get('/admin/faqs', [WebController::class, 'adminFaqs'])->name('web.admin.faqs');
    Route::get('/admin/slides', [WebController::class, 'adminSlides'])->name('web.admin.slides');
    Route::get('/admin/pages', [WebController::class, 'adminPages'])->name('web.admin.pages');
    Route::get('/admin/settings', [WebController::class, 'adminSettings'])->name('web.admin.settings.general');
    Route::get('/admin/settings/site', [WebController::class, 'adminSettingsSite'])->name('web.admin.settings.site');
    Route::post('/admin/settings/site', [WebController::class, 'adminSettingsSiteUpdate'])->name('web.admin.settings.site.update');
    Route::get('/admin/settings/users', [WebController::class, 'adminSettingsUsers'])->name('web.admin.settings.users');
    Route::get('/admin/settings/roles', [WebController::class, 'adminSettingsRoles'])->name('web.admin.settings.roles');
    Route::get('/admin/settings/notifications', [WebController::class, 'adminSettingsNotifications'])->name('web.admin.settings.notifications');
    Route::post('/admin/settings/notifications', [WebController::class, 'adminSettingsNotificationsUpdate'])->name('web.admin.settings.notifications.update');
    Route::get('/admin/settings/system', [WebController::class, 'adminSettingsSystem'])->name('web.admin.settings.system');

    // Staff
    Route::get('/staff/orders', [WebController::class, 'staffOrders'])->name('web.staff.orders');
    Route::get('/staff/inventory', [WebController::class, 'staffInventory'])->name('web.staff.inventory');
    Route::get('/staff/franchise-stock', [WebController::class, 'staffFranchiseStock'])->name('web.staff.franchiseStock');

    // Finance
    Route::get('/finance/payments', [WebController::class, 'financePayments'])->name('web.finance.payments');
    Route::get('/finance/reports', [WebController::class, 'financeReports'])->name('web.finance.reports');

    // Franchise
    Route::get('/franchise/orders', [WebController::class, 'franchiseOrders'])->name('web.franchise.orders');
    Route::get('/franchise/sales', [WebController::class, 'franchiseSales'])->name('web.franchise.sales');
    Route::get('/franchise/inventory', [WebController::class, 'franchiseInventory'])->name('web.franchise.inventory');
    Route::get('/franchise/payments', [WebController::class, 'franchisePayments'])->name('web.franchise.payments');
    Route::get('/franchise/chat', [WebController::class, 'franchiseChat'])->name('web.franchise.chat');
});
