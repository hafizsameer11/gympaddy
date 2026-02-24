<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BusinessController extends Controller
{
    private function sendBusinessStatusEmail(Business $business, string $status, ?string $reason = null): void
    {
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

        // Send status email once when status changes to approved/rejected.
        if ($status !== $previousStatus && in_array($status, ['approved', 'rejected'])) {
            try {
                $this->sendBusinessStatusEmail($business, $status, $business->rejected_reason);
            } catch (\Throwable $e) {
                Log::warning('Business status email failed to send', [
                    'business_id' => $business->id,
                    'user_id' => $business->user_id,
                    'status' => $status,
                    'email' => $business->user?->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['message' => 'Business status updated successfully', 'data' => $business, 'status' => 'success']);
    }
}
