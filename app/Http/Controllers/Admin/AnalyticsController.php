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
    private function periodToDays(string $period): int
    {
        return match ($period) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            'all' => 0,
            default => 30,
        };
    }

    private function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function getOverallAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            $days = $this->periodToDays($period);

            $isAll = $days === 0;
            $startDate = $isAll ? null : Carbon::now()->subDays($days)->startOfDay();

            $usersQuery = User::query();
            $revenueQuery = Transaction::where('type', 'topup')->where('status', 'completed');
            $transactionsQuery = Transaction::query();

            if ($startDate) {
                $currentUsers = (clone $usersQuery)->where('created_at', '>=', $startDate)->count();
                $currentRevenue = (float) (clone $revenueQuery)->where('created_at', '>=', $startDate)->sum('amount');
                $currentTransactions = (clone $transactionsQuery)->where('created_at', '>=', $startDate)->count();

                $prevStart = Carbon::now()->subDays($days * 2)->startOfDay();
                $previousUsers = (clone $usersQuery)->whereBetween('created_at', [$prevStart, $startDate])->count();
                $previousRevenue = (float) (clone $revenueQuery)->whereBetween('created_at', [$prevStart, $startDate])->sum('amount');
                $previousTransactions = (clone $transactionsQuery)->whereBetween('created_at', [$prevStart, $startDate])->count();
            } else {
                $currentUsers = $usersQuery->count();
                $currentRevenue = (float) $revenueQuery->sum('amount');
                $currentTransactions = $transactionsQuery->count();
                $previousUsers = 0;
                $previousRevenue = 0;
                $previousTransactions = 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => [
                        'total' => $currentUsers,
                        'growth' => $this->calculateGrowth($currentUsers, $previousUsers),
                    ],
                    'revenue' => [
                        'total' => $currentRevenue,
                        'growth' => $this->calculateGrowth($currentRevenue, $previousRevenue),
                    ],
                    'transactions' => [
                        'total' => $currentTransactions,
                        'growth' => $this->calculateGrowth($currentTransactions, $previousTransactions),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            $days = $this->periodToDays($period);
            if ($days === 0) $days = 730;

            $labels = [];
            $newUsersData = [];
            $activeUsersData = [];

            $step = $days > 90 ? 7 : 1;
            for ($i = $days - 1; $i >= 0; $i -= $step) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('M d');
                if ($step > 1) {
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->addDays($step - 1)->endOfDay();
                    $newUsersData[] = User::whereBetween('created_at', [$start, $end])->count();
                    $activeUsersData[] = User::whereBetween('updated_at', [$start, $end])->count();
                } else {
                    $newUsersData[] = User::whereDate('created_at', $date)->count();
                    $activeUsersData[] = User::whereDate('updated_at', $date)->count();
                }
            }

            $periodStart = Carbon::now()->subDays($days)->startOfDay();
            $newUsersInPeriod = User::where('created_at', '>=', $periodStart)->count();
            $totalUsers = User::count();
            $prevStart = Carbon::now()->subDays($days * 2)->startOfDay();
            $prevNewUsers = User::whereBetween('created_at', [$prevStart, $periodStart])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'chartData' => [
                        'labels' => $labels,
                        'datasets' => [
                            ['label' => 'New Users', 'data' => $newUsersData],
                            ['label' => 'Active Users', 'data' => $activeUsersData],
                        ],
                    ],
                    'summary' => [
                        'totalUsers' => $totalUsers,
                        'newUsers' => $newUsersInPeriod,
                        'activeUsers' => User::where('status', 'online')->count(),
                        'growth' => $this->calculateGrowth($newUsersInPeriod, $prevNewUsers),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getRevenueAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            $days = $this->periodToDays($period);
            if ($days === 0) $days = 730;

            $labels = [];
            $revenueData = [];

            $step = $days > 90 ? 7 : 1;
            for ($i = $days - 1; $i >= 0; $i -= $step) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('M d');
                if ($step > 1) {
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->addDays($step - 1)->endOfDay();
                    $revenueData[] = (float) Transaction::where('type', 'topup')
                        ->where('status', 'completed')
                        ->whereBetween('created_at', [$start, $end])
                        ->sum('amount');
                } else {
                    $revenueData[] = (float) Transaction::where('type', 'topup')
                        ->where('status', 'completed')
                        ->whereDate('created_at', $date)
                        ->sum('amount');
                }
            }

            $periodStart = Carbon::now()->subDays($days)->startOfDay();
            $totalRevenue = (float) Transaction::where('type', 'topup')
                ->where('status', 'completed')
                ->where('created_at', '>=', $periodStart)
                ->sum('amount');
            $averageRevenue = count($revenueData) > 0 ? $totalRevenue / count($revenueData) : 0;

            $prevStart = Carbon::now()->subDays($days * 2)->startOfDay();
            $prevRevenue = (float) Transaction::where('type', 'topup')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$prevStart, $periodStart])
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'chartData' => [
                        'labels' => $labels,
                        'datasets' => [
                            ['label' => 'Revenue', 'data' => $revenueData],
                        ],
                    ],
                    'summary' => [
                        'totalRevenue' => $totalRevenue,
                        'averageRevenue' => round($averageRevenue, 2),
                        'growth' => $this->calculateGrowth($totalRevenue, $prevRevenue),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAdsAnalytics(Request $request)
    {
        try {
            $period = $request->query('period', '30d');
            $days = $this->periodToDays($period);
            if ($days === 0) $days = 730;

            $labels = [];
            $impressionsData = [];
            $clicksData = [];

            $step = $days > 90 ? 7 : 1;
            for ($i = $days - 1; $i >= 0; $i -= $step) {
                $endDate = Carbon::today()->subDays($i);
                $startDate = $step > 1 ? $endDate->copy()->subDays($step - 1) : $endDate;
                $labels[] = $step > 1 ? $startDate->format('M d') : $endDate->format('M d');
                $impressionsData[] = (int) AdCampaign::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])->sum('impressions');
                $clicksData[] = (int) AdCampaign::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])->sum('clicks');
            }

            $periodStart = Carbon::now()->subDays($days)->startOfDay();
            $totalImpressions = (int) AdCampaign::where('created_at', '>=', $periodStart)->sum('impressions');
            $totalClicks = (int) AdCampaign::where('created_at', '>=', $periodStart)->sum('clicks');
            $totalSpent = (float) AdCampaign::where('created_at', '>=', $periodStart)->sum('spent');
            $averageCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'chartData' => [
                        'labels' => $labels,
                        'datasets' => [
                            ['label' => 'Impressions', 'data' => $impressionsData],
                            ['label' => 'Clicks', 'data' => $clicksData],
                        ],
                    ],
                    'summary' => [
                        'totalImpressions' => $totalImpressions,
                        'totalClicks' => $totalClicks,
                        'averageCTR' => round($averageCTR, 1),
                        'totalSpent' => $totalSpent,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
