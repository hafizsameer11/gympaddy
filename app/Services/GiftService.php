<?php

namespace App\Services;

use App\Models\Gift;

class GiftService
{
    public function index()
    {
        $userId = auth()->id();
        
        $gifts = Gift::with(['sender', 'receiver'])
            ->where(function($query) use ($userId) {
                $query->where('from_user_id', $userId)
                      ->orWhere('to_user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($gift) use ($userId) {
                $isSent = $gift->from_user_id === $userId;
                
                return [
                    'id' => $gift->id,
                    'from_user_id' => $gift->from_user_id,
                    'to_user_id' => $gift->to_user_id,
                    'amount' => $gift->amount,
                    'message' => $gift->message,
                    'name' => $gift->name,
                    'value' => $gift->value,
                    'created_at' => $gift->created_at,
                    'updated_at' => $gift->updated_at,
                    'type' => $isSent ? 'sent' : 'received',
                    'recipient' => $isSent ? $gift->receiver->fullname : $gift->sender->fullname,
                    'description' => $isSent 
                        ? "You sent {$gift->name} to {$gift->receiver->fullname}"
                        : "You received {$gift->name} from {$gift->sender->fullname}",
                    'date' => $gift->created_at->format('d/m/y'),
                    'timestamp' => $gift->created_at->format('h:i A'),
                ];
            });

        return response()->json($gifts);
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['from_user_id'] = $user->id;
        $data['amount'] = $data['value'];
        $gift = Gift::create($data);
        return response()->json($gift, 201);
    }

    public function show(Gift $gift)
    {
        return $gift;
    }

    public function update(Gift $gift, $validated)
    {
        $gift->update($validated);
        return response()->json($gift);
    }

    public function destroy(Gift $gift)
    {
        $gift->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
