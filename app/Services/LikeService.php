<?php

namespace App\Services;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;

class LikeService
{
    public function __construct(
        protected PushNotificationService $pushNotificationService
    ) {
    }
    public function index()
    {
        return Like::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;

        $alreadyLiked = Like::where([
            'user_id' => $data['user_id'],
            'likeable_id' => $data['likeable_id'],
            'likeable_type' => $data['likeable_type'],
        ])->exists();

        if ($alreadyLiked) {
            return response()->json([
                'status' => 'error',
                'code' => 409,
                'message' => 'Already liked',
                'errors' => [[
                    'field' => 'like',
                    'reason' => 'User has already liked this item',
                    'suggestion' => 'You cannot like the same item more than once'
                ]],
            ], 409);
        }

        $like = Like::create($data);
        $this->notifyLikeOwner($like, $user->id);
        return response()->json($like, 201);
    }
    public function likePost($payload)
    {
        $user = auth()->user();
        $payload['user_id'] = $user->id;

        // Check if the user has already liked this post
        $alreadyLiked = Like::where([
            'user_id' => $payload['user_id'],
            'likeable_id' => $payload['likeable_id'],
        ])->exists();

        if ($alreadyLiked) {
            //disliek it
            Like::where([
                'user_id' => $payload['user_id'],
                'likeable_id' => $payload['likeable_id'],
            ])->delete();
            return response()->json(['message' => 'Disliked'], 200);
        }

        $like = Like::create($payload);
        $this->notifyLikeOwner($like, $user->id);
        return response()->json($like, 201);
    }

    protected function notifyLikeOwner(Like $like, int $likerUserId): void
    {
        $ownerId = null;
        $type = strtolower((string) $like->likeable_type);

        if (in_array($type, ['post', 'app\\models\\post'], true)) {
            $post = Post::find($like->likeable_id);
            $ownerId = $post?->user_id;
        }

        if (!$ownerId || (int) $ownerId === $likerUserId) {
            return;
        }

        $liker = User::find($likerUserId);
        $this->pushNotificationService->notifyUser(
            (int) $ownerId,
            'New like',
            ($liker?->username ?? 'Someone') . ' liked your post.',
            'like',
            ['post_id' => (string) $like->likeable_id, 'liker_id' => (string) $likerUserId]
        );
    }

    public function show(Like $like)
    {
        return $like;
    }

    public function update(Like $like, $validated)
    {
        $like->update($validated);
        return response()->json($like);
    }

    public function destroy(Like $like)
    {
        $like->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
