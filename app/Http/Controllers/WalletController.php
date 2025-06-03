<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Wallet::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'balance' => 'required|numeric',
        ]);
        $data['user_id'] = $request->user()->id;
        $wallet = Wallet::create($data);
        return response()->json($wallet, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        return $wallet;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wallet $wallet)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        $data = $request->validate([
            'balance' => 'sometimes|numeric',
        ]);
        $wallet->update($data);
        return response()->json($wallet);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Top up the wallet balance.
     */
    public function topup(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $wallet = Wallet::where('user_id', $request->user()->id)->firstOrFail();
        $wallet->balance += $request->amount;
        $wallet->save();

        // Create transaction record
        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'topup',
            'amount' => $request->amount,
            'reference' => null,
            'related_user_id' => null,
            'meta' => null,
            'status' => 'completed',
        ]);

        return response()->json($wallet);
    }

    /**
     * Withdraw from the wallet balance.
     */
    public function withdraw(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $wallet = Wallet::where('user_id', $request->user()->id)->firstOrFail();
        if ($wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }
        $wallet->balance -= $request->amount;
        $wallet->save();

        // Create transaction record
        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'withdraw',
            'amount' => $request->amount,
            'reference' => null,
            'related_user_id' => null,
            'meta' => null,
            'status' => 'completed',
        ]);

        return response()->json($wallet);
    }
}
