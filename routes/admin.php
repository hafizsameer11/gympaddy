<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SocialController;
use App\Http\Controllers\Admin\MarketController;
use App\Http\Controllers\Admin\ConnectController;
use App\Http\Controllers\Admin\GymController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\AdsController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\SettingsController;

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    
    // Dashboard Endpoints
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/latest-users', [DashboardController::class, 'latestUsers']);
    Route::get('/dashboard/latest-posts', [DashboardController::class, 'latestPosts']);
    Route::get('/dashboard/user-statistics', [DashboardController::class, 'userStatistics']);

    // User Management Endpoints
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'getAllUsers']);
        Route::get('/stats', [UserManagementController::class, 'getUserStats']);
        Route::get('/stats-by-section', [UserManagementController::class, 'getUserStatsBySection']);
        Route::get('/username/{username}', [UserManagementController::class, 'getUserByUsername']);
        Route::get('/{id}', [UserManagementController::class, 'userDetails']);
        Route::post('/', [UserManagementController::class, 'createUser']);
        Route::put('/{id}', [UserManagementController::class, 'updateUser']);
        Route::delete('/{id}', [UserManagementController::class, 'deleteUser']);
        Route::post('/{id}/ban', [UserManagementController::class, 'banUser']);
        Route::post('/{id}/unban', [UserManagementController::class, 'unbanUser']);
    });

    // Existing User Management Routes (keep for backward compatibility)
    Route::prefix('user-management')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::get('/details/{id}', [UserManagementController::class, 'userDetails']);
        Route::get('/social/{id}', [UserManagementController::class, 'socialData']);
        Route::get('/marketPlace/{userId}', [UserManagementController::class, 'getmarketPlaceListingForUser']);
        Route::get('/chat/{id}', [UserManagementController::class, 'getUserChats']);
        Route::get('/transactions/{id}', [UserManagementController::class, 'getUserTransactions']);
    });

    // Social Management Endpoints
    Route::prefix('social')->group(function () {
        Route::get('/posts', [SocialController::class, 'getAllPosts']);
        Route::get('/posts/user/{userId}', [SocialController::class, 'getUserPosts']);
        Route::get('/posts/{id}', [SocialController::class, 'getPostById']);
        Route::post('/posts/{id}/hide', [SocialController::class, 'hidePost']);
        Route::delete('/posts/{id}', [SocialController::class, 'deletePost']);
        Route::get('/statuses', [SocialController::class, 'getAllStatuses']);
        Route::get('/statuses/user/{userId}', [SocialController::class, 'getUserStatuses']);
        Route::delete('/statuses/{id}', [SocialController::class, 'deleteStatus']);
        Route::get('/live', [SocialController::class, 'getAllLiveStreams']);
        Route::get('/live/user/{userId}', [SocialController::class, 'getUserLiveStreams']);
        Route::post('/live/{id}/end', [SocialController::class, 'endLiveStream']);
        Route::delete('/live/{id}', [SocialController::class, 'deleteLiveStream']);
        Route::get('/stats', [SocialController::class, 'getSocialStats']);
    });

    // Market Management Endpoints
    Route::prefix('market')->group(function () {
        Route::get('/listings', [MarketController::class, 'getAllListings']);
        Route::get('/listings/{id}', [MarketController::class, 'getListingById']);
        Route::get('/listings/user/{userId}', [MarketController::class, 'getUserListings']);
        Route::post('/listings', [MarketController::class, 'createListing']);
        Route::put('/listings/{id}', [MarketController::class, 'updateListing']);
        Route::delete('/listings/{id}', [MarketController::class, 'deleteListing']);
        Route::post('/listings/{id}/boost', [MarketController::class, 'boostListing']);
        Route::get('/stats', [MarketController::class, 'getMarketStats']);
    });

    // Connect Management Endpoints
    Route::prefix('connect')->group(function () {
        Route::get('/users', [ConnectController::class, 'getAllConnectUsers']);
        Route::get('/users/{id}', [ConnectController::class, 'getConnectUserById']);
        Route::get('/matches/{userId}', [ConnectController::class, 'getUserMatches']);
        Route::get('/stats', [ConnectController::class, 'getConnectStats']);
    });

    // Gym Management Endpoints
    Route::prefix('gym')->group(function () {
        Route::get('/gyms', [GymController::class, 'getAllGyms']);
        Route::get('/gyms/{id}', [GymController::class, 'getGymById']);
        Route::post('/gyms', [GymController::class, 'createGym']);
        Route::put('/gyms/{id}', [GymController::class, 'updateGym']);
        Route::delete('/gyms/{id}', [GymController::class, 'deleteGym']);
        Route::get('/stats', [GymController::class, 'getGymStats']);
    });

    // Transaction Management Endpoints
    Route::prefix('transactions')->group(function () {
        Route::get('/stats', [AdminTransactionController::class, 'stats']);
        Route::get('/', [AdminTransactionController::class, 'index']);
        Route::get('/user/{userId}', [AdminTransactionController::class, 'userTransactions']);
        Route::get('/{id}', [AdminTransactionController::class, 'show']);
    });

    // Existing Transaction Management Route (keep for backward compatibility)
    Route::prefix('transaction-management')->group(function () {
        Route::get('/', [AdminTransactionController::class, 'index']);
    });

    // Subscription Endpoints
    Route::prefix('subscriptions')->group(function () {
        Route::get('/stats', [SubscriptionController::class, 'getSubscriptionStats']);
        Route::get('/', [SubscriptionController::class, 'getAllSubscriptions']);
        Route::get('/user/{userId}', [SubscriptionController::class, 'getUserSubscription']);
        Route::get('/{id}', [SubscriptionController::class, 'getSubscriptionById']);
        Route::post('/', [SubscriptionController::class, 'createSubscription']);
        Route::put('/{id}', [SubscriptionController::class, 'updateSubscription']);
        Route::post('/{id}/cancel', [SubscriptionController::class, 'cancelSubscription']);
    });

    // Verification Endpoints
    Route::prefix('verifications')->group(function () {
        Route::get('/stats', [VerificationController::class, 'getVerificationStats']);
        Route::get('/user/{userId}', [VerificationController::class, 'getVerificationByUser']);
        Route::get('/', [VerificationController::class, 'getAllVerifications']);
        Route::get('/{id}', [VerificationController::class, 'getVerificationById']);
        Route::post('/{id}/approve', [VerificationController::class, 'approveVerification']);
        Route::post('/{id}/reject', [VerificationController::class, 'rejectVerification']);
    });

    // Ads Management Endpoints
    Route::prefix('ads')->group(function () {
        Route::get('/stats', [AdsController::class, 'getAdsStats']);
        Route::get('/', [AdsController::class, 'getAllAds']);
        Route::get('/{id}', [AdsController::class, 'getAdById']);
        Route::post('/', [AdsController::class, 'createAd']);
        Route::put('/{id}', [AdsController::class, 'updateAd']);
        Route::delete('/{id}', [AdsController::class, 'deleteAd']);
        Route::post('/{id}/pause', [AdsController::class, 'pauseAd']);
        Route::post('/{id}/resume', [AdsController::class, 'resumeAd']);
    });

    // Analytics Endpoints
    Route::prefix('analytics')->group(function () {
        Route::get('/', [AnalyticsController::class, 'getOverallAnalytics']);
        Route::get('/users', [AnalyticsController::class, 'getUserAnalytics']);
        Route::get('/revenue', [AnalyticsController::class, 'getRevenueAnalytics']);
        Route::get('/ads', [AnalyticsController::class, 'getAdsAnalytics']);
    });

    // Notification Endpoints
    Route::prefix('notifications')->group(function () {
        Route::get('/', [AdminNotificationController::class, 'getAllNotifications']);
        Route::post('/send', [AdminNotificationController::class, 'sendNotification']);
        Route::post('/send-bulk', [AdminNotificationController::class, 'sendBulkNotification']);
        Route::get('/{id}', [AdminNotificationController::class, 'getNotificationById']);
        Route::put('/{id}', [AdminNotificationController::class, 'updateNotificationStatus']);
        Route::delete('/{id}', [AdminNotificationController::class, 'deleteNotification']);
        Route::post('/{id}/read', [AdminNotificationController::class, 'markAsRead']);
    });

    // Support Endpoints
    Route::prefix('support')->group(function () {
        Route::get('/tickets', [SupportController::class, 'getAllTickets']);
        Route::get('/tickets/{id}', [SupportController::class, 'getTicketById']);
        Route::post('/tickets', [SupportController::class, 'createTicket']);
        Route::put('/tickets/{id}', [SupportController::class, 'updateTicket']);
        Route::post('/tickets/{id}/close', [SupportController::class, 'closeTicket']);
    });

    // Admin Management Endpoints
    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminManagementController::class, 'getAllAdmins']);
        Route::get('/{id}', [AdminManagementController::class, 'getAdminById']);
        Route::post('/', [AdminManagementController::class, 'createAdmin']);
        Route::put('/{id}', [AdminManagementController::class, 'updateAdmin']);
        Route::delete('/{id}', [AdminManagementController::class, 'deleteAdmin']);
    });

    // Settings Endpoints
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'getSettings']);
        Route::put('/', [SettingsController::class, 'updateSettings']);
    });

    // Existing Business Management Routes (keep for backward compatibility)
    Route::prefix('business-management')->group(function () {
        Route::get('/', [AdminBusinessController::class, 'index']);
        Route::post('/update-status/{id}', [AdminBusinessController::class, 'updateStatus']);
    });
});
