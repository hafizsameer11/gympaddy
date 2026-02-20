<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ConnectController extends Controller
{
    public function getAllConnectUsers(Request $request)
    {
        try {
            $query = User::query();

            if ($request->has('subscription') && $request->subscription !== 'all') {
                $subscribed = $request->subscription === 'true';
                $query->where('subscription_status', $subscribed ? 'active' : 'inactive');
            }

            if ($request->has('videoVerification') && $request->videoVerification !== 'all') {
                $query->where('video_verification_status', $request->videoVerification);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $users = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users->items(),
                    'pagination' => [
                        'currentPage' => $users->currentPage(),
                        'totalPages' => $users->lastPage(),
                        'totalItems' => $users->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getConnectUserById($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found']], 404);
            }
            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserMatches($userId)
    {
        try {
            $matches = [];
            return response()->json(['success' => true, 'data' => $matches]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getConnectStats()
    {
        try {
            $totalUsers = User::count();
            $subscribedUsers = User::where('subscription_status', 'active')->count();
            $verifiedUsers = User::where('video_verification_status', 'verified')->count();
            $totalMatches = 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUsers' => $totalUsers,
                    'subscribedUsers' => $subscribedUsers,
                    'verifiedUsers' => $verifiedUsers,
                    'totalMatches' => $totalMatches,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
