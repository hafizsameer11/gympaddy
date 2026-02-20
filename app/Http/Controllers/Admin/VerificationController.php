<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function getAllVerifications(Request $request)
    {
        try {
            $query = Business::with('user:id,username,fullname,email');

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $verifications = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $formattedVerifications = $verifications->getCollection()->map(function ($business) {
                return [
                    'id' => 'verify_' . $business->id,
                    'userId' => $business->user_id,
                    'userName' => $business->user->fullname ?? 'Unknown',
                    'businessName' => $business->business_name,
                    'category' => $business->category ?? 'Gym',
                    'status' => $business->status ?? 'pending',
                    'documents' => $business->documents ? json_decode($business->documents) : [],
                    'createdAt' => $business->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'verifications' => $formattedVerifications,
                    'pagination' => [
                        'currentPage' => $verifications->currentPage(),
                        'totalPages' => $verifications->lastPage(),
                        'totalItems' => $verifications->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getVerificationById($id)
    {
        try {
            $businessId = str_replace('verify_', '', $id);
            $business = Business::with('user:id,username,fullname,email')->find($businessId);
            
            if (!$business) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Verification not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => 'verify_' . $business->id,
                    'userId' => $business->user_id,
                    'userName' => $business->user->fullname ?? 'Unknown',
                    'userEmail' => $business->user->email ?? '',
                    'businessName' => $business->business_name,
                    'category' => $business->category ?? 'Gym',
                    'status' => $business->status ?? 'pending',
                    'documents' => $business->documents ? json_decode($business->documents) : [],
                    'notes' => $business->notes ?? '',
                    'createdAt' => $business->created_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function approveVerification(Request $request, $id)
    {
        try {
            $businessId = str_replace('verify_', '', $id);
            $business = Business::find($businessId);
            
            if (!$business) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Verification not found']], 404);
            }

            $business->update([
                'status' => 'approved',
                'notes' => $request->notes ?? 'All documents verified'
            ]);

            return response()->json(['success' => true, 'message' => 'Verification approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function rejectVerification(Request $request, $id)
    {
        try {
            $businessId = str_replace('verify_', '', $id);
            $business = Business::find($businessId);
            
            if (!$business) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Verification not found']], 404);
            }

            $business->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason ?? 'Incomplete documentation'
            ]);

            return response()->json(['success' => true, 'message' => 'Verification rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getVerificationByUser($userId)
    {
        try {
            $business = Business::with('user:id,username,fullname,email,phone,profile_picture')
                ->where('user_id', $userId)
                ->first();

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'NOT_FOUND', 'message' => 'No verification found for this user']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id'             => 'verify_' . $business->id,
                    'userId'         => $business->user_id,
                    'userName'       => $business->user->fullname ?? 'Unknown',
                    'userEmail'      => $business->user->email ?? '',
                    'userPhone'      => $business->user->phone ?? '',
                    'profilePicture' => $business->user->profile_picture ?? null,
                    'businessName'   => $business->business_name,
                    'category'       => $business->category ?? 'Gym',
                    'businessEmail'  => $business->business_email ?? '',
                    'businessPhone'  => $business->business_phone ?? '',
                    'photo'          => $business->photo ?? null,
                    'status'         => $business->status ?? 'pending',
                    'documents'      => $business->documents ? json_decode($business->documents) : [],
                    'notes'          => $business->notes ?? '',
                    'createdAt'      => $business->created_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    public function getVerificationStats()
    {
        try {
            $totalVerifications = Business::count();
            $pendingVerifications = Business::where('status', 'pending')->count();
            $approvedVerifications = Business::where('status', 'approved')->count();
            $rejectedVerifications = Business::where('status', 'rejected')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalVerifications' => $totalVerifications,
                    'pendingVerifications' => $pendingVerifications,
                    'approvedVerifications' => $approvedVerifications,
                    'rejectedVerifications' => $rejectedVerifications,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
