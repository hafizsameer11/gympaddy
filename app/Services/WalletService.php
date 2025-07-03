<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;

class WalletService
{
    public function index()
    {
        return Wallet::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $wallet = Wallet::create($data);
        return response()->json($wallet, 201);
    }

    public function show(Wallet $wallet)
    {
        return $wallet;
    }

    public function update(Wallet $wallet, $validated)
    {
        $wallet->update($validated);
        return response()->json($wallet);
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return response()->json(['message' => 'Deleted']);
    }

public function topup($user, $validated)
{
    $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

    logger()->info('[Topup] BEFORE:', [
        'user_id' => $user->id,
        'current_balance' => $wallet->balance,
        'topup_amount' => $validated['amount'],
    ]);

    $wallet->balance += $validated['amount'];

    if ($wallet->isDirty('balance')) {
        logger()->info('[Topup] Wallet is dirty. Proceeding to save.');
        $wallet->save();
    } else {
        logger()->warning('[Topup] Wallet not dirty. Nothing to save.');
    }

    logger()->info('[Topup] AFTER:', [
        'new_balance' => $wallet->fresh()->balance
    ]);

    Transaction::create([
        'wallet_id' => $wallet->id,
        'type' => 'topup',
        'amount' => $validated['amount'],
        'reference' => null,
        'related_user_id' => null,
        'meta' => null,
        'status' => 'completed',
    ]);

    return response()->json($wallet->fresh());
}


    public function withdraw($user, $validated)
    {
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        if ($wallet->balance < $validated['amount']) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }
        $wallet->balance -= $validated['amount'];
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'withdraw',
            'amount' => $validated['amount'],
            'reference' => null,
            'related_user_id' => null,
            'meta' => null,
            'status' => 'completed',
        ]);

        return response()->json($wallet);
    }
}
