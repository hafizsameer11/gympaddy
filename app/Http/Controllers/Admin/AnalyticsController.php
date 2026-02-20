<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\AdCampaign;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function getOverallAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            
            $totalUsers = User::count();
            $totalRevenue = Transaction::where('type', 'topup')->where('status', 'completed')->sum('amount');
            $totalTransactions = Transaction::count();

            $previousPeriodUsers = User::where('created_at', '<', Carbon::now()->subDays(30))->count();
            $userGrowth = $previousPeriodUsers > 0 ? (($totalUsers - $previousPeriodUsers) / $previousPeriodUsers) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => [
                        'total' => $totalUsers,
                        'growth' => round($userGrowth, 1)
                    ],
                    'revenue' => [
                        'total' => (float) $totalRevenue,
                        'growth' => 8.5
                    ],
                    'transactions' => [
                        'total' => $totalTransactions,
                        'growth' => 3.2
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserAnalytics(Request $request)
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
            $newUsersData = [];
            $activeUsersData = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('M d');
                $newUsersData[] = User::whereDate('created_at', $date)->count();
                $activeUsersData[] = User::whereDate('updated_at', $date)->count();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'chartData' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'New Users',
                                'data' => $newUsersData
                            ],
                            [
                                'label' => 'Active Users',
                                'data' => $activeUsersData
                            ]
                        ]
                    ],
                    'summary' => [
                        'totalUsers' => User::count(),
                        'newUsers' => array_sum($newUsersData),
                        'activeUsers' => User::where('status', 'online')->count(),
                        'growth' => 5.2
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getRevenueAnalytics(Request $request)
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
            $revenueData = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('M d');
                $revenueData[] = Transaction::where('type', 'topup')
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('amount');
            }

            $totalRevenue = array_sum($revenueData);
            $averageRevenue = count($revenueData) > 0 ? $totalRevenue / count($revenueData) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'chartData' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Revenue',
                                'data' => $revenueData
                            ]
                        ]
                    ],
                    'summary' => [
                        'totalRevenue' => (float) $totalRevenue,
                        'averageRevenue' => round($averageRevenue, 2),
                        'growth' => 8.5
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAdsAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            
            $totalImpressions = AdCampaign::sum('impressions');
            $totalClicks = AdCampaign::sum('clicks');
            $totalSpent = AdCampaign::sum('spent');
            $averageCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'chartData' => [
                        'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        'datasets' => [
                            [
                                'label' => 'Impressions',
                                'data' => [50000, 60000, 65000, 75000]
                            ],
                            [
                                'label' => 'Clicks',
                                'data' => [1500, 1800, 2000, 2200]
                            ]
                        ]
                    ],
                    'summary' => [
                        'totalImpressions' => $totalImpressions,
                        'totalClicks' => $totalClicks,
                        'averageCTR' => round($averageCTR, 1),
                        'totalSpent' => (float) $totalSpent
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
