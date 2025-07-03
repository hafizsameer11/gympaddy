<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function index($user)
    {
        $wallets = $user->wallets()->pluck('id');
        return Transaction::whereIn('wallet_id', $wallets)->get();
    }

    public function store($validated)
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        $amount = $validated['amount'];
        $gpcoin = $amount / 2000;
        Log::info($gpcoin);
        Log::info('Validated data: ', $validated);
        $validated['wallet_id'] = $wallet->id;
        $wallet->balance += $gpcoin;
        $wallet->save();
        $transaction = Transaction::create($validated);
        return response()->json($transaction, 201);
    }

    public function show(Transaction $transaction)
    {
        return $transaction;
    }

    public function update(Transaction $transaction, $validated)
    {
        $transaction->update($validated);
        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function getForUser($userId)
    {
        $wallets = Wallet::where('user_id', $userId)->orderBy('created_at', 'desc')->first();
        $transactions = Transaction::where('wallet_id', $wallets->id)->get();
        $totalTopup = $transactions->where('type', 'topup')->sum('amount');
        $totalWithdraw = $transactions->where('type', 'withdraw')->sum('amount');
        $currentBalance = $wallets->balance ?? 0;
        return [
            'transactions' => $transactions,
            'totalTopup' => $totalTopup,
            'totalWithdraw' => $totalWithdraw,
            'currentBalance' => $currentBalance,
        ];
    }
}
