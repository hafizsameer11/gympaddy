<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        try {
            $totalUsers = User::count();
            $totalRevenue = Transaction::where('type', 'topup')
                ->where('status', 'completed')
                ->sum('amount');
            $totalTransactions = Transaction::count();
            
            $activeSubscriptions = User::where('subscription_status', 'active')->count();
            
            $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
            
            $revenueToday = Transaction::where('type', 'topup')
                ->where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUsers' => $totalUsers,
                    'totalRevenue' => (float) $totalRevenue,
                    'totalTransactions' => $totalTransactions,
                    'activeSubscriptions' => $activeSubscriptions,
                    'newUsersToday' => $newUsersToday,
                    'revenueToday' => (float) $revenueToday,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function latestUsers()
    {
        try {
            $users = User::orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'id'             => $user->id,
                        'fullName'       => $user->fullname,
                        'username'       => $user->username,
                        'email'          => $user->email,
                        'phoneNumber'    => $user->phone,
                        'age'            => $user->age,
                        'lastLogin'      => $user->updated_at->toIso8601String(),
                        'profileImage'   => $user->profile_picture,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function latestPosts()
    {
        try {
            $posts = Post::with('user:id,username,fullname,profile_picture')
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($post) {
                    return [
                        'id'              => $post->id,
                        'description'     => $post->content ?? 'New post',
                        'time'            => $post->created_at->diffForHumans(),
                        'profile_picture' => $post->user->profile_picture ?? null,
                        'userName'        => $post->user->fullname ?? $post->user->username ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function userStatistics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            
            $days = match($period) {
                '7d' => 7,
                '30d' => 30,
                '90d' => 90,
                '1y' => 365,
                default => 30
            };

            $labels = [];
            $data = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('M d');
                $data[] = User::whereDate('created_at', $date)->count();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'New Users',
                            'data' => $data
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
