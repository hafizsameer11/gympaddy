<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    private function sendBusinessStatusEmail(Business $business, string $status, ?string $reason = null): void
    {
        $business->loadMissing('user');
        $email = $business->user?->email;
        if (!$email) {
            return;
        }

        $businessName = $business->business_name ?: 'your business profile';

        if ($status === 'approved') {
            $body = "Congratulations! Your business account has been approved.\n\nBusiness: {$businessName}\n\nYou can now view your approved business profile in the app.";
            $subject = 'Business Account Approved';
        } elseif ($status === 'rejected') {
            $rejectionReason = $reason ?: ($business->rejected_reason ?: 'Please review your details and try again.');
            $body = "Your business account request was not approved.\n\nBusiness: {$businessName}\nReason: {$rejectionReason}\n\nPlease update your details and submit again.";
            $subject = 'Business Account Update';
        } else {
            return;
        }

        Mail::raw($body, function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }

    private function formatVerification(Business $business): array
    {
        $user = $business->user;
        $approvedBy = $business->approvedBy;
        $rejectedBy = $business->rejectedBy;

        return [
            'id'            => 'verify_' . $business->id,
            'userId'        => $business->user_id,
            'userName'      => $user->fullname ?? 'Unknown',
            'userEmail'     => $user->email ?? '',
            'userPhone'     => $user->phone ?? '',
            'profilePicture'=> $user->profile_picture ?? null,
            'username'      => $user->username ?? '',
            'businessName'  => $business->business_name ?? '',
            'category'      => $business->category ?? 'Gym',
            'businessEmail' => $business->business_email ?? '',
            'businessPhone' => $business->business_phone ?? '',
            'photo'         => $business->photo ?? null,
            'status'        => $business->status ?? 'pending',
            'approvedByName'=> $approvedBy?->fullname ?? $approvedBy?->username ?? null,
            'rejectedByName'=> $rejectedBy?->fullname ?? $rejectedBy?->username ?? null,
            'documents'     => $business->documents ? json_decode($business->documents) : [],
            'notes'         => $business->notes ?? '',
            'created_at'    => $business->created_at->format('d/m/y'),
            'createdAt'     => $business->created_at->toIso8601String(),
        ];
    }

    public function getAllVerifications(Request $request)
    {
        try {
            $query = Business::with([
                'user:id,username,fullname,email,phone,profile_picture',
                'approvedBy:id,username,fullname,email,phone,profile_picture',
                'rejectedBy:id,username,fullname,email,phone,profile_picture',
            ]);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $page  = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $verifications = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $formattedVerifications = $verifications->getCollection()
                ->map(fn($b) => $this->formatVerification($b));

            return response()->json([
                'success' => true,
                'data' => [
                    'verifications' => $formattedVerifications,
                    'pagination' => [
                        'currentPage' => $verifications->currentPage(),
                        'totalPages'  => $verifications->lastPage(),
                        'totalItems'  => $verifications->total(),
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
            $business = Business::with([
                'user:id,username,fullname,email,phone,profile_picture',
                'approvedBy:id,username,fullname,email,phone,profile_picture',
                'rejectedBy:id,username,fullname,email,phone,profile_picture',
            ])->find($businessId);
            
            if (!$business) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Verification not found']], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatVerification($business),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function approveVerification(Request $request, $id)
    {
        try {
            $businessId = str_replace('verify_', '', $id);
            $business = Business::with('user')->find($businessId);
            
            if (!$business) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Verification not found']], 404);
            }

            $admin = Auth::user();
            if (!$admin) {
                return response()->json(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Admin not authenticated']], 401);
            }

            $previousStatus = $business->status;
            $business->update([
                'status' => 'approved',
                'notes' => $request->notes ?? 'All documents verified',
                'approved_by' => $admin->id,
                'rejected_by' => null,
            ]);

            if ($previousStatus !== 'approved') {
                try {
                    $this->sendBusinessStatusEmail($business, 'approved');
                } catch (\Throwable $e) {
                    Log::warning('Business approval email failed to send', [
                        'business_id' => $business->id,
                        'user_id' => $business->user_id,
                        'email' => $business->user?->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Verification approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function rejectVerification(Request $request, $id)
    {
        try {
            $businessId = str_replace('verify_', '', $id);
            $business = Business::with('user')->find($businessId);
            
            if (!$business) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Verification not found']], 404);
            }

            $admin = Auth::user();
            if (!$admin) {
                return response()->json(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Admin not authenticated']], 401);
            }

            $previousStatus = $business->status;
            $business->update([
                'status' => 'rejected',
                'rejected_reason' => $request->reason ?? 'Incomplete documentation',
                'rejected_by' => $admin->id,
                'approved_by' => null,
            ]);

            if ($previousStatus !== 'rejected') {
                try {
                    $this->sendBusinessStatusEmail($business, 'rejected', $request->reason);
                } catch (\Throwable $e) {
                    Log::warning('Business rejection email failed to send', [
                        'business_id' => $business->id,
                        'user_id' => $business->user_id,
                        'email' => $business->user?->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Verification rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getVerificationByUser($userId)
    {
        try {
            $business = Business::with([
                'user:id,username,fullname,email,phone,profile_picture',
                'approvedBy:id,username,fullname,email,phone,profile_picture',
                'rejectedBy:id,username,fullname,email,phone,profile_picture',
            ])
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
                    'approvedByName'=> $business->approvedBy?->fullname ?? $business->approvedBy?->username ?? null,
                    'rejectedByName'=> $business->rejectedBy?->fullname ?? $business->rejectedBy?->username ?? null,
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
