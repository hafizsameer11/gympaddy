<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Models\LiveStream;
use App\Models\LiveStreamChat;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LiveStreamChatController extends Controller
{
    public function index($id)
    {
        $chats = LiveStreamChat::with(['user', 'replyTo.user'])
            ->where('live_stream_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $chats,
        ]);
    }

    public function store(Request $request, $id)
    {
        $type = $request->input('type', 'message');

        $request->validate([
            'message' => 'required|string|max:1000',
            'type' => ['nullable', 'string', Rule::in(['message', 'gift'])],
            'reply_to_id' => 'nullable|exists:live_stream_chats,id',
            'amount' => [
                Rule::requiredIf($type === 'gift'),
                'nullable',
                'numeric',
                'min:0.01',
            ],
        ]);

        $stream = LiveStream::find($id);
        if (!$stream || !$stream->is_active || ($stream->status ?? '') === 'ended') {
            return response()->json([
                'status' => false,
                'message' => 'This live stream has ended. Chat is closed.',
            ], 410);
        }

        Log::info('Live stream chat', ['stream_id' => $id, 'type' => $type, 'user_id' => auth()->id()]);

        if ($type === 'gift') {
            return $this->storeGiftMessage($request, $id, $stream);
        }

        $chat = LiveStreamChat::create([
            'user_id' => auth()->id(),
            'live_stream_id' => $id,
            'message' => $request->message,
            'type' => 'message',
            'amount' => null,
            'reply_to_id' => $request->reply_to_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $chat->load(['user', 'replyTo.user']),
        ]);
    }

    /**
     * Transfer GP from viewer to host, record gifts row, persist chat with amount.
     */
    protected function storeGiftMessage(Request $request, int|string $id, LiveStream $stream)
    {
        $senderId = auth()->id();
        $receiverId = (int) $stream->user_id;
        $coinAmount = (float) $request->amount;

        if ($senderId === $receiverId) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot send a gift to your own live stream.',
            ], 422);
        }

        try {
            $chat = DB::transaction(function () use ($request, $id, $senderId, $receiverId, $coinAmount) {
                Wallet::firstOrCreate(
                    ['user_id' => $senderId],
                    ['balance' => 0]
                );
                Wallet::firstOrCreate(
                    ['user_id' => $receiverId],
                    ['balance' => 0]
                );

                $senderWallet = Wallet::where('user_id', $senderId)->lockForUpdate()->first();
                $receiverWallet = Wallet::where('user_id', $receiverId)->lockForUpdate()->first();

                if ((float) $senderWallet->balance < $coinAmount) {
                    throw new HttpResponseException(response()->json([
                        'status' => false,
                        'message' => 'Insufficient GP balance to send this gift.',
                    ], 422));
                }

                $senderWallet->balance = (float) $senderWallet->balance - $coinAmount;
                $receiverWallet->balance = (float) $receiverWallet->balance + $coinAmount;
                $senderWallet->save();
                $receiverWallet->save();

                $giftName = str_starts_with($request->message, 'Sent ')
                    ? mb_substr($request->message, 0, 255)
                    : 'Live stream gift';

                Gift::create([
                    'from_user_id' => $senderId,
                    'to_user_id' => $receiverId,
                    'amount' => $coinAmount,
                    'value' => $coinAmount,
                    'name' => $giftName,
                    'message' => $request->message,
                ]);

                return LiveStreamChat::create([
                    'user_id' => $senderId,
                    'live_stream_id' => $id,
                    'message' => $request->message,
                    'type' => 'gift',
                    'amount' => $coinAmount,
                    'reply_to_id' => $request->reply_to_id,
                ]);
            });
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Live stream gift failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Could not process gift. Please try again.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Gift sent successfully',
            'data' => $chat->load(['user', 'replyTo.user']),
            'sender_balance' => (float) Wallet::where('user_id', $senderId)->value('balance'),
        ]);
    }
}
