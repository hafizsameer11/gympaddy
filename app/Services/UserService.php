<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Transaction;
use App\Models\Gift;
use App\Models\Like;
use App\Models\Share;
use App\Models\UserProfile;
class UserService
{
    public function index()
    {
        return User::paginate(20);
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function store($validated)
    {
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function update($id, $validated)
    {
        $user = User::findOrFail($id);
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function userCount(){
        return User::where('role', 'user')->count();

    }
    public function allUsers(){
        return User::where('role','user')->orderBy('created_at','desc')->get();
    }
  public function getUserById($id){
    return User::with(
        'wallet', 
        'transactions', 
        'giftsReceived',
        'notifications', 
        'posts', 
        'comments',
        'giftsSent',
        'profile',
        'wallets'
    )->findOrFail($id);
}
public function getUserSocialData($id)
{
    // Overall stats
    $totalPosts = Post::where('user_id', $id)->count();
    $totalComments = Comment::where('user_id', $id)->count();
    $totalShares = Share::where('user_id', $id)->count();
    $totalLikes = Like::where('user_id', $id)->count();
    $posts = Post::where('user_id', $id)
        ->withCount(['likes', 'shares'])
        ->with([
            'media',
            'boostedCampaign',
            'allComments.user' // load comment author
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    // Format post data
    $postData = $posts->map(function ($post) {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'created_at' => $post->created_at,
            'is_boosted' => (bool) $post->is_boosted,
            'likes_count' => $post->likes_count,
            'shares_count' => $post->shares_count,
            'media' => $post->media->map(fn ($m) => asset('storage/' . $m->path)),
            'boosted_campaign' => $post->boostedCampaign ? [
                'id' => $post->boostedCampaign->id,
                'budget' => $post->boostedCampaign->budget ?? null,
                'status' => $post->boostedCampaign->status ?? null
            ] : null,
            'comments' => $post->allComments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id ?? null,
                        'name' => $comment->user->fullname ?? 'Unknown',
                        'profile_picture' => $comment->user->profile_picture_url ?? null,
                    ],
                    'created_at' => $comment->created_at,
                    'parent_id' => $comment->parent_id
                ];
            })
        ];
    });

    return [
        'total_posts' => $totalPosts,
        'total_comments' => $totalComments,
        'total_shares_by_user' => $totalShares,
        'total_likes_by_user' => $totalLikes,
        'posts' => $postData
    ];
}

}