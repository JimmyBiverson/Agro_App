<?php

use App\Http\Controllers\WebController;
use App\Http\Middleware\AuthenticateWeb;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('web.dashboard'));

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
    Route::post('/admin/users', [WebController::class, 'adminStoreUser'])->name('web.admin.users.store');
    Route::post('/admin/users/delete', [WebController::class, 'adminDeleteUser'])->name('web.admin.users.delete');
    Route::get('/admin/products', [WebController::class, 'adminProducts'])->name('web.admin.products');
    Route::post('/admin/products', [WebController::class, 'adminStoreProduct'])->name('web.admin.products.store');
    Route::post('/admin/products/delete', [WebController::class, 'adminDeleteProduct'])->name('web.admin.products.delete');
    Route::get('/admin/categories', [WebController::class, 'adminCategories'])->name('web.admin.categories');
    Route::post('/admin/categories', [WebController::class, 'adminStoreCategory'])->name('web.admin.categories.store');
    Route::post('/admin/categories/delete', [WebController::class, 'adminDeleteCategory'])->name('web.admin.categories.delete');
    Route::get('/admin/orders', [WebController::class, 'adminOrders'])->name('web.admin.orders');
    Route::post('/admin/orders/{id}/approve', [WebController::class, 'adminApproveOrder'])->name('web.admin.orders.approve');
    Route::post('/admin/orders/{id}/decline', [WebController::class, 'adminDeclineOrder'])->name('web.admin.orders.decline');
    Route::get('/admin/payments', [WebController::class, 'adminPayments'])->name('web.admin.payments');
    Route::post('/admin/payments/{id}/accept', [WebController::class, 'adminAcceptPayment'])->name('web.admin.payments.accept');
    Route::post('/admin/payments/{id}/reject', [WebController::class, 'adminRejectPayment'])->name('web.admin.payments.reject');
    Route::get('/admin/reports', [WebController::class, 'adminReports'])->name('web.admin.reports');
    Route::get('/admin/audit', [WebController::class, 'adminAudit'])->name('web.admin.audit');
    Route::get('/admin/news', [WebController::class, 'adminNews'])->name('web.admin.news');
    Route::post('/admin/news', [WebController::class, 'adminStoreNews'])->name('web.admin.news.store');
    Route::post('/admin/news/delete', [WebController::class, 'adminDeleteNews'])->name('web.admin.news.delete');
    Route::get('/admin/faqs', [WebController::class, 'adminFaqs'])->name('web.admin.faqs');
    Route::post('/admin/faqs', [WebController::class, 'adminStoreFaq'])->name('web.admin.faqs.store');
    Route::post('/admin/faqs/delete', [WebController::class, 'adminDeleteFaq'])->name('web.admin.faqs.delete');
    Route::get('/admin/slides', [WebController::class, 'adminSlides'])->name('web.admin.slides');
    Route::post('/admin/slides', [WebController::class, 'adminStoreSlide'])->name('web.admin.slides.store');
    Route::post('/admin/slides/delete', [WebController::class, 'adminDeleteSlide'])->name('web.admin.slides.delete');
    Route::get('/admin/pages', [WebController::class, 'adminPages'])->name('web.admin.pages');
    Route::post('/admin/pages', [WebController::class, 'adminStorePage'])->name('web.admin.pages.store');
    Route::post('/admin/pages/delete', [WebController::class, 'adminDeletePage'])->name('web.admin.pages.delete');
    Route::get('/admin/settings', [WebController::class, 'adminSettings'])->name('web.admin.settings.general');
    Route::get('/admin/settings/site', [WebController::class, 'adminSettingsSite'])->name('web.admin.settings.site');
    Route::post('/admin/settings/site', [WebController::class, 'adminSettingsSiteUpdate'])->name('web.admin.settings.site.update');
    Route::get('/admin/settings/users', [WebController::class, 'adminSettingsUsers'])->name('web.admin.settings.users');
    Route::get('/admin/settings/roles', [WebController::class, 'adminSettingsRoles'])->name('web.admin.settings.roles');
    Route::get('/admin/settings/notifications', [WebController::class, 'adminSettingsNotifications'])->name('web.admin.settings.notifications');
    Route::post('/admin/settings/notifications', [WebController::class, 'adminSettingsNotificationsUpdate'])->name('web.admin.settings.notifications.update');
    Route::get('/admin/settings/system', [WebController::class, 'adminSettingsSystem'])->name('web.admin.settings.system');

    // Stock Movements
    Route::get('/admin/stock-movements', [WebController::class, 'adminStockMovements'])->name('web.admin.stockMovements');

    // Report Exports
    Route::get('/admin/reports/export', [WebController::class, 'adminReportExport'])->name('web.admin.reports.export');

    // Admin Password Reset
    Route::post('/admin/users/reset-password', [WebController::class, 'adminResetPassword'])->name('web.admin.users.resetPassword');

    // Staff
    Route::get('/staff/orders', [WebController::class, 'staffOrders'])->name('web.staff.orders');
    Route::post('/staff/orders/{id}/approve', [WebController::class, 'staffApproveOrder'])->name('web.staff.orders.approve');
    Route::post('/staff/orders/{id}/decline', [WebController::class, 'staffDeclineOrder'])->name('web.staff.orders.decline');
    Route::get('/staff/inventory', [WebController::class, 'staffInventory'])->name('web.staff.inventory');
    Route::get('/staff/franchise-stock', [WebController::class, 'staffFranchiseStock'])->name('web.staff.franchiseStock');

    // Finance
    Route::get('/finance/payments', [WebController::class, 'financePayments'])->name('web.finance.payments');
    Route::post('/finance/payments/{id}/accept', [WebController::class, 'financeAcceptPayment'])->name('web.finance.payments.accept');
    Route::post('/finance/payments/{id}/reject', [WebController::class, 'financeRejectPayment'])->name('web.finance.payments.reject');
    Route::get('/finance/reports', [WebController::class, 'financeReports'])->name('web.finance.reports');

    // Franchise
    Route::get('/franchise/orders', [WebController::class, 'franchiseOrders'])->name('web.franchise.orders');
    Route::get('/franchise/sales', [WebController::class, 'franchiseSales'])->name('web.franchise.sales');
    Route::get('/franchise/inventory', [WebController::class, 'franchiseInventory'])->name('web.franchise.inventory');
    Route::get('/franchise/payments', [WebController::class, 'franchisePayments'])->name('web.franchise.payments');
    Route::get('/franchise/chat', [WebController::class, 'franchiseChat'])->name('web.franchise.chat');
});
