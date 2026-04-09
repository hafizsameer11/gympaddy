<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\LiveStream;
use App\Models\Transaction;
use App\Models\Wallet;

class LiveStreamService
{
  public function index()
{
    // Host sends heartbeat every ~30s; without it (app killed), hide from discover immediately
    // instead of waiting only for the scheduled cleanup job.
    $threshold = now()->subMinutes(2);

    $liveStreams = LiveStream::with([
        'user',
        'user.latestImagePost.media',
    ])
    ->withCount([
        'audiences as current_viewers_count' => function ($q) {
            $q->whereNull('left_at');
        },
    ])
    ->where('is_active', 1)
    ->where(function ($q) {
        $q->whereNull('status')->orWhere('status', 'active');
    })
    ->where(function ($q) use ($threshold) {
        // No heartbeat yet: host still joining (do not require updated_at — slow setups stay listed).
        // Once heartbeats exist, require a recent one so dead apps drop off the discover list immediately.
        $q->whereNull('last_heartbeat_at')
            ->orWhere('last_heartbeat_at', '>=', $threshold);
    })
    ->latest()
    ->get();

    return response()->json($liveStreams);
}

    public function store($user, $validated)
    {
        $liveCost = (float) (AppSetting::getValue('live_cost', '0') ?? 0);

        if ($liveCost > 0) {
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            }

            $balance = (float) ($wallet->balance ?? 0);
            if ($balance < $liveCost) {
                return response()->json([
                    'message' => 'Insufficient balance. Live streaming costs ₦' . number_format($liveCost, 2) . '. Please top up your wallet.',
                ], 402);
            }

            $wallet->balance = $balance - $liveCost;
            $wallet->save();

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw',
                'amount' => $liveCost,
                'reference' => null,
                'related_user_id' => null,
                'meta' => 'Live stream',
                'status' => 'completed',
            ]);
        }

        $data = $validated;
        $data['user_id'] = $user->id;
        $liveStream = LiveStream::create($data);
        return response()->json($liveStream, 201);
    }

    public function show(LiveStream $liveStream)
    {
        return $liveStream;
    }

    public function update(LiveStream $liveStream, $validated)
    {
        $liveStream->update($validated);
        return response()->json($liveStream);
    }

    public function destroy(LiveStream $liveStream)
    {
        $liveStream->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
