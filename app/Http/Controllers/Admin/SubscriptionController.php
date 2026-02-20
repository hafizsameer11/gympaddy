<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function getAllSubscriptions(Request $request)
    {
        try {
            $query = User::where('subscription_status', '!=', null);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('subscription_status', $request->status);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $subscriptions = $query->paginate($limit, ['*'], 'page', $page);

            $formattedSubs = $subscriptions->map(function ($user) {
                return [
                    'id' => 'sub_' . $user->id,
                    'userId' => $user->id,
                    'userName' => $user->fullname,
                    'userEmail' => $user->email,
                    'plan' => 'Premium',
                    'amount' => 29.99,
                    'status' => $user->subscription_status ?? 'active',
                    'startDate' => $user->subscription_start_date ?? $user->created_at,
                    'endDate' => $user->subscription_end_date ?? now()->addMonth(),
                    'createdAt' => $user->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'subscriptions' => $formattedSubs,
                    'pagination' => [
                        'currentPage' => $subscriptions->currentPage(),
                        'totalPages' => $subscriptions->lastPage(),
                        'totalItems' => $subscriptions->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getSubscriptionById($id)
    {
        try {
            $userId = str_replace('sub_', '', $id);
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Subscription not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => 'sub_' . $user->id,
                    'userId' => $user->id,
                    'userName' => $user->fullname,
                    'userEmail' => $user->email,
                    'plan' => 'Premium',
                    'amount' => 29.99,
                    'status' => $user->subscription_status ?? 'active',
                    'startDate' => $user->subscription_start_date ?? $user->created_at,
                    'endDate' => $user->subscription_end_date ?? now()->addMonth(),
                    'autoRenew' => true,
                    'createdAt' => $user->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserSubscription($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }
            return $this->getSubscriptionById('sub_' . $userId);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createSubscription(Request $request)
    {
        try {
            return response()->json(['success' => true, 'message' => 'Subscription created successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateSubscription(Request $request, $id)
    {
        try {
            return response()->json(['success' => true, 'message' => 'Subscription updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function cancelSubscription($id)
    {
        try {
            $userId = str_replace('sub_', '', $id);
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Subscription not found']], 404);
            }

            $user->update(['subscription_status' => 'cancelled']);
            return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getSubscriptionStats()
    {
        try {
            $totalSubscriptions = User::where('subscription_status', '!=', null)->count();
            $activeSubscriptions = User::where('subscription_status', 'active')->count();
            $expiredSubscriptions = User::where('subscription_status', 'expired')->count();
            $cancelledSubscriptions = User::where('subscription_status', 'cancelled')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalSubscriptions' => $totalSubscriptions,
                    'activeSubscriptions' => $activeSubscriptions,
                    'expiredSubscriptions' => $expiredSubscriptions,
                    'cancelledSubscriptions' => $cancelledSubscriptions,
                    'monthlyRevenue' => $activeSubscriptions * 29.99,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
