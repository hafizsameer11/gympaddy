<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

class TransactionService
{
    public function index($user)
    {
        $wallets = $user->wallets()->pluck('id');
        return Transaction::whereIn('wallet_id', $wallets)->get();
    }

    public function store($validated)
    {
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
        $wallets = Wallet::where('user_id', $userId)->orderBy('created_at', 'desc')->pluck('id');
        return Transaction::whereIn('wallet_id', $wallets)->get();
    }
}
