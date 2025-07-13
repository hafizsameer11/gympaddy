<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
{
    $query = $request->input('q');

    // Search users
    $users = User::where('username', 'like', "%{$query}%")
        // ->select('id', 'username', 'profile_picture_url')
        ->limit(20)
        ->get();

    // Search posts using same relationships used in index()
    $posts = Post::with(['user', 'media', 'comments.user', 'likes'])
        ->where('content', 'like', "%{$query}%")
        ->orWhere('title', 'like', "%{$query}%")
        ->latest()
        ->limit(20)
        ->get();

    return response()->json([
        'status' => true,
        'users' => $users,
        'posts' => $posts,
    ]);
}

}
