<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(){
        $transactions=Transaction::with(['wallet.user'])->orderBy('created_at', 'desc')->get();
        $totalTransactions = $transactions->count();
        $totalTopup = $transactions->where('type', 'topup')->sum('amount');
        $totalWithdraw = $transactions->where('type', 'withdraw')->sum('amount');
        $totalTransfer = $transactions->where('type', 'transfer')->sum('amount');
        $totalGift = $transactions->where('type', 'gift')->sum('amount');
        $data=[
            'transactions'=>$transactions,
            'totalTransactions' => $totalTransactions,
            'totalTopup' => $totalTopup,
            'totalWithdraw' => $totalWithdraw,
            'totalTransfer' => $totalTransfer,
            'totalGift' => $totalGift,
        ];
        return response()->json(['message' => 'Transactions retrieved successfully', 'data' => $data, 'status' => 'success']);
    }
}
