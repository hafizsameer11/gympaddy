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
            'profile_picture' => $user->profile_picture ?? null,
            'amount'          => number_format((float) $tx->amount, 2),
            'type'            => $tx->type === 'withdraw' ? 'withdrawal' : $tx->type,
            'status'          => $tx->status,
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

    public function stats()
    {
        try {
            $totalTransactions = Transaction::count();
            $totalRevenue = Transaction::where('type', 'topup')
                ->where('status', 'completed')
                ->sum('amount');
            $totalDeposits = Transaction::where('type', 'topup')
                ->where('status', 'completed')
                ->sum('amount');
            $totalWithdrawals = Transaction::where('type', 'withdraw')
                ->where('status', 'completed')
                ->sum('amount');
            $pendingTransactions = Transaction::where('status', 'pending')->count();
            $completedTransactions = Transaction::where('status', 'completed')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalTransactions'   => $totalTransactions,
                    'totalRevenue'        => (float) $totalRevenue,
                    'totalDeposits'       => (float) $totalDeposits,
                    'totalWithdrawals'    => (float) $totalWithdrawals,
                    'pendingTransactions' => $pendingTransactions,
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
