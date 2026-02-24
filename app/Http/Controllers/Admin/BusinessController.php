<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BusinessController extends Controller
{
    public function index()
    {
        $totalBusinesss = Business::count();
        $verifiedBusinesss = Business::where('status', 'approved')->count();
        $pendingBusinesss = Business::where('status', 'pending')->count();
        $rejectedBusinesss = Business::where('status', 'rejected')->count();
        $business = Business::with('user')->orderBy('created_at', 'desc')->get();
        $data = [
            'totalBusinesss' => $totalBusinesss,
            'verifiedBusinesss' => $verifiedBusinesss,
            'pendingBusinesss' => $pendingBusinesss,
            'rejectedBusinesss' => $rejectedBusinesss,
            'business' => $business
        ];
        return response()->json(['message' => 'Business data retrieved successfully', 'data' => $data, 'status' => 'success']);
    }
    public function updateStatus(Request $request, $id)
    {
        $business = Business::with('user')->findOrFail($id);
        $previousStatus = $business->status;
        $status = $request->input('status');

        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $business->status = $status;
        //if status is rejected than therre should be a rejectec_reason field in the request
        if ($status === 'rejected') {
            $rejectedReason = $request->input('rejected_reason');
            if (!$rejectedReason) {
                $rejectedReason = 'Please Try Again'; // Default rejected reason if not provided
            }
            $business->rejected_reason = $rejectedReason;
        } else {
            $business->rejected_reason = null; // Clear rejected reason if status is not rejected

        }
        $business->save();

        // Send approval email once when status transitions to approved.
        if ($status === 'approved' && $previousStatus !== 'approved' && $business->user?->email) {
            try {
                $businessName = $business->business_name ?: 'your business profile';
                Mail::raw(
                    "Congratulations! Your business account has been approved.\n\nBusiness: {$businessName}\n\nYou can now view your approved business profile in the app.",
                    function ($message) use ($business) {
                        $message->to($business->user->email)
                            ->subject('Business Account Approved');
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Business approval email failed to send', [
                    'business_id' => $business->id,
                    'user_id' => $business->user_id,
                    'email' => $business->user?->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['message' => 'Business status updated successfully', 'data' => $business, 'status' => 'success']);
    }
}
