<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Only show transactions for the authenticated user's wallet(s)
        $wallets = $request->user()->wallets()->pluck('id');
        return Transaction::whereIn('wallet_id', $wallets)->get();
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
        $validator = \Validator::make($request->all(), [
            'wallet_id' => 'required|integer|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:topup,withdraw,gift,purchase,ad,other',
            'reference' => 'nullable|string|max:255',
            'related_user_id' => 'nullable|integer|exists:users,id',
            'meta' => 'nullable',
            'status' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            Log::warning('Transaction creation validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation Failed',
                'errors' => collect($validator->errors())->map(function($messages, $field) {
                    return [
                        'field' => $field,
                        'reason' => $messages[0],
                        'suggestion' => 'Please provide a valid value'
                    ];
                })->values(),
            ], 422);
        }
        $transaction = Transaction::create($validator->validated());
        return response()->json($transaction, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return $transaction;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validator = \Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'type' => 'sometimes|required|in:topup,withdraw,gift,purchase,ad,other',
            'reference' => 'nullable|string|max:255',
            'related_user_id' => 'nullable|integer|exists:users,id',
            'meta' => 'nullable',
            'status' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            Log::warning('Transaction update validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation Failed',
                'errors' => collect($validator->errors())->map(function($messages, $field) {
                    return [
                        'field' => $field,
                        'reason' => $messages[0],
                        'suggestion' => 'Please provide a valid value'
                    ];
                })->values(),
            ], 422);
        }
        $transaction->update($validator->validated());
        return response()->json($transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
