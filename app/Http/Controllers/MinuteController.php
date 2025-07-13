<?php

namespace App\Http\Controllers;

use App\Models\Minute;
use App\Models\MinutePurchaseHistory;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MinuteController extends Controller
{
    public function  getMinutes()
    {
        $user = Auth::user();
        $minutes = Minute::where('user_id', $user->id)->first();
        //check if it exists otherwise create it
        if ($minutes == null) {
            $minutes = new Minute();
            $minutes->user_id = $user->id;
            $minutes->save();
        }
        return response()->json(['status' => 'success', 'data' => $minutes]);
    }
    public function purchaseMinute(Request $request)
    {
        $user = Auth::user();
        // $type = $request->type;
        $minute = $request->minutes;
        $purchaseMinuteHistory = MinutePurchaseHistory::create([
            'user_id' => $user->id,
            'amount' => $minute,
            'minutes' => $minute

        ]);
        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($wallet == null) {
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->save();
        }
        $wallet->balance = $wallet->balance - $minute;
        $wallet->save();
        //also save the minutes
        $minutes = Minute::where('user_id', $user->id)->first();
        $minutes->live_stream_minute = $minutes->live_stream_minute + $minute;
        $minutes->save();
        return response()->json(['status' => 'success', 'data' => $purchaseMinuteHistory]);
    }
}
