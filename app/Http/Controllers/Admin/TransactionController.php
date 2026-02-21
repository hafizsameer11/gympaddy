<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private function formatTransaction(Transaction $tx): array
    {
        $user = $tx->wallet->user ?? null;

        return [
            'id'              => $tx->reference ?? (string) $tx->id,
            'transactionId'   => $tx->id,
            'fullName'        => $user->fullname ?? 'Unknown',
            'username'        => $user->username ?? null,
            'profile_picture' => $user->profile_picture ?? null,
            'amount'          => (float) $tx->amount,
            'type'            => $tx->type === 'withdraw' ? 'withdrawal' : $tx->type,
            'status'          => $tx->status ?? 'pending',
            'date'            => $tx->created_at->format('d/m/y - h:i A'),
            'description'     => $tx->meta ?? null,
        ];
    }

    public function index()
    {
        try {
            $transactions = Transaction::with(['wallet.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($tx) => $this->formatTransaction($tx));

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $tx = Transaction::with(['wallet.user'])->find($id);
            if (!$tx) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'NOT_FOUND', 'message' => 'Transaction not found']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatTransaction($tx),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $totalTransactions    = Transaction::count();
            $pendingTransactions  = Transaction::where('status', 'pending')->count();
            $completedTransactions = Transaction::where('status', 'completed')->count();

            $totalDeposits    = (float) Transaction::where('type', 'topup')->sum('amount');
            $totalWithdrawals = (float) Transaction::where('type', 'withdraw')->sum('amount');
            $totalRevenue     = $totalDeposits - $totalWithdrawals;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalTransactions'     => $totalTransactions,
                    'totalRevenue'          => $totalRevenue,
                    'totalDeposits'         => $totalDeposits,
                    'totalWithdrawals'      => $totalWithdrawals,
                    'pendingTransactions'   => $pendingTransactions,
                    'completedTransactions' => $completedTransactions,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    public function userTransactions($userId)
    {
        try {
            $transactions = Transaction::whereHas('wallet', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->with(['wallet.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($tx) => $this->formatTransaction($tx));

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]
            ], 500);
        }
    }
}
