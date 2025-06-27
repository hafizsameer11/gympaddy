<?php

use App\Http\Controllers\PersonalAccessTokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\AdCampaignController;
use App\Http\Controllers\AdInsightController;
use App\Http\Controllers\MarketplaceListingController;
use App\Http\Controllers\MarketplaceCategoryController;
use App\Http\Controllers\LiveStreamController;
use App\Http\Controllers\ReelController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VideoCallController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Personal access tokens
Route::get('personal-access-tokens', [PersonalAccessTokenController::class, 'index']);
Route::post('personal-access-tokens', [PersonalAccessTokenController::class, 'store']);
Route::get('personal-access-tokens/{id}', [PersonalAccessTokenController::class, 'show']);
Route::put('personal-access-tokens/{id}', [PersonalAccessTokenController::class, 'update']);
Route::delete('personal-access-tokens/{id}', [PersonalAccessTokenController::class, 'destroy']);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // User Profile
    Route::get('profile', [UserController::class, 'profile']);
    Route::post('edit-profile', [UserController::class, 'editProfile']);

    // Posts
    Route::get('posts', [PostController::class, 'index']);
    Route::post('posts', [PostController::class, 'store']);
    Route::get('posts/{post}', [PostController::class, 'show']);
    Route::put('posts/{post}', [PostController::class, 'update']);
    Route::delete('posts/{post}', [PostController::class, 'destroy']);

    // Comments
    Route::get('comments', [CommentController::class, 'index']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::get('comments/{comment}', [CommentController::class, 'show']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    // Wallets
    Route::get('wallets', [WalletController::class, 'index']);
    Route::post('wallets', [WalletController::class, 'store']);
    Route::get('wallets/{wallet}', [WalletController::class, 'show']);
    Route::put('wallets/{wallet}', [WalletController::class, 'update']);
    Route::delete('wallets/{wallet}', [WalletController::class, 'destroy']);
    Route::post('wallet/topup', [WalletController::class, 'topup']);
    Route::post('wallet/withdraw', [WalletController::class, 'withdraw']);

    // Gifts
    Route::get('gifts', [GiftController::class, 'index']);
    Route::post('gifts', [GiftController::class, 'store']);
    Route::get('gifts/{gift}', [GiftController::class, 'show']);
    Route::put('gifts/{gift}', [GiftController::class, 'update']);
    Route::delete('gifts/{gift}', [GiftController::class, 'destroy']);

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy']);

    // Businesses
    Route::get('businesses', [BusinessController::class, 'index']);
    Route::post('businesses', [BusinessController::class, 'store']);
    Route::get('businesses/{business}', [BusinessController::class, 'show']);
    Route::put('businesses/{business}', [BusinessController::class, 'update']);
    Route::delete('businesses/{business}', [BusinessController::class, 'destroy']);

    // Ad Campaigns
    Route::get('ad-campaigns', [AdCampaignController::class, 'index']);
    Route::post('ad-campaigns', [AdCampaignController::class, 'store']);
    Route::get('ad-campaigns/{ad_campaign}', [AdCampaignController::class, 'show']);
    Route::put('ad-campaigns/{ad_campaign}', [AdCampaignController::class, 'update']);
    Route::delete('ad-campaigns/{ad_campaign}', [AdCampaignController::class, 'destroy']);

    // Ad Insights
    Route::apiResource('ad-insights', AdInsightController::class)->only(['index', 'show']);

    // Marketplace Listings
    Route::get('marketplace-listings', [MarketplaceListingController::class, 'index']);
    Route::post('marketplace-listings', [MarketplaceListingController::class, 'store']);
    Route::get('marketplace-listings/{marketplace_listing}', [MarketplaceListingController::class, 'show']);
    Route::put('marketplace-listings/{marketplace_listing}', [MarketplaceListingController::class, 'update']);
    Route::delete('marketplace-listings/{marketplace_listing}', [MarketplaceListingController::class, 'destroy']);

    // Marketplace Categories
    Route::get('marketplace-categories', [MarketplaceCategoryController::class, 'index']);
    Route::post('marketplace-categories', [MarketplaceCategoryController::class, 'store']);
    Route::get('marketplace-categories/{marketplace_category}', [MarketplaceCategoryController::class, 'show']);
    Route::put('marketplace-categories/{marketplace_category}', [MarketplaceCategoryController::class, 'update']);
    Route::delete('marketplace-categories/{marketplace_category}', [MarketplaceCategoryController::class, 'destroy']);

    // Live Streams
    Route::get('live-streams', [LiveStreamController::class, 'index']);
    Route::post('live-streams', [LiveStreamController::class, 'store']);
    Route::get('live-streams/{live_stream}', [LiveStreamController::class, 'show']);
    Route::put('live-streams/{live_stream}', [LiveStreamController::class, 'update']);
    Route::delete('live-streams/{live_stream}', [LiveStreamController::class, 'destroy']);

    // Reels
    Route::get('reels', [ReelController::class, 'index']);
    Route::post('reels', [ReelController::class, 'store']);
    Route::get('reels/{reel}', [ReelController::class, 'show']);
    Route::put('reels/{reel}', [ReelController::class, 'update']);
    Route::delete('reels/{reel}', [ReelController::class, 'destroy']);

    // Likes
    Route::get('likes', [LikeController::class, 'index']);
    Route::post('likes', [LikeController::class, 'store']);
    Route::get('likes/{like}', [LikeController::class, 'show']);
    Route::put('likes/{like}', [LikeController::class, 'update']);
    Route::delete('likes/{like}', [LikeController::class, 'destroy']);

    // Shares
    Route::get('shares', [ShareController::class, 'index']);
    Route::post('shares', [ShareController::class, 'store']);
    Route::get('shares/{share}', [ShareController::class, 'show']);
    Route::put('shares/{share}', [ShareController::class, 'update']);
    Route::delete('shares/{share}', [ShareController::class, 'destroy']);

    // Follows
    Route::get('follows', [FollowController::class, 'index']);
    Route::post('follows', [FollowController::class, 'store']);
    Route::get('follows/{follow}', [FollowController::class, 'show']);
    Route::put('follows/{follow}', [FollowController::class, 'update']);
    Route::delete('follows/{follow}', [FollowController::class, 'destroy']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications', [NotificationController::class, 'store']);
    Route::get('notifications/{notification}', [NotificationController::class, 'show']);
    Route::put('notifications/{notification}', [NotificationController::class, 'update']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

    // Chat Messages
    Route::get('chat-messages', [ChatMessageController::class, 'index']);
    Route::post('chat-messages', [ChatMessageController::class, 'store']);
    Route::get('chat-messages/{chat_message}', [ChatMessageController::class, 'show']);
    Route::put('chat-messages/{chat_message}', [ChatMessageController::class, 'update']);
    Route::delete('chat-messages/{chat_message}', [ChatMessageController::class, 'destroy']);

    // Tickets
    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('tickets/{ticket}', [TicketController::class, 'update']);
    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy']);

    // Video Calls
    Route::get('video-calls', [VideoCallController::class, 'index']);
    Route::post('video-calls', [VideoCallController::class, 'store']);
    Route::get('video-calls/{video_call}', [VideoCallController::class, 'show']);
    Route::put('video-calls/{video_call}', [VideoCallController::class, 'update']);
    Route::delete('video-calls/{video_call}', [VideoCallController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard']);
    Route::apiResource('users', AdminUserController::class);
    // ...other admin endpoints...
});
