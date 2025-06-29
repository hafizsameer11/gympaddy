<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

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
        $business = Business::findOrFail($id);
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
            $business->save();

            return response()->json(['message' => 'Business status updated successfully', 'data' => $business, 'status' => 'success']);
        }
    }
}
