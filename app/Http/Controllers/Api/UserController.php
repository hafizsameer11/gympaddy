<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Follow;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Edit user profile with optional image upload
     */
    public function editProfile(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id)
            ],
            'fullname' => 'sometimes|string|max:255',
            'age' => 'sometimes|integer|min:1|max:120',
            'gender' => 'sometimes|in:male,female,other',
            'profile_picture' => 'sometimes|image|mimes:jpeg,jpg,png,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $file = $request->file('profile_picture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');

            $validated['profile_picture'] = $path;
        }

        // Update user with validated data
        $user->update($validated);

        // Reload user to get updated data
        $user->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get current user profile
     */
    public function profile()
    {
        $user = Auth::user();
        //send followers count, following count, total post count and all posts
        $followersCount = Follow::where('followed_id', $user->id)->count();
        $followingCount = Follow::where('follower_id', $user->id)->count();
        $postCount = Post::where('user_id', $user->id)->count();
        $posts = Post::where('user_id', $user->id)->with(['likes', 'comments', 'media'])->get();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'post_count' => $postCount,
            'posts' => $posts
        ]);
    }

    /**
     * Update device token
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string|max:255',
        ]);
        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json(['message' => 'Device token updated']);
    }
    public function userDetails($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        //get follower count its list total post count and all posts
        $followersCount = Follow::where('followed_id', $userId)->count();
        $followingCount = Follow::where('follower_id', $userId)->count();
        $postCount = Post::where('user_id', $userId)->count();
        $posts = Post::where('user_id', $userId)->with(['likes', 'comments', 'media'])->get();
        //check if authenticated user is following this user
        $isFollowing = Follow::where('follower_id', Auth::id())
            ->where('followed_id', $userId)
            ->exists();
            $business=Business::where('user_id', $userId)->where('status','approved')->exists();
            //check does user have Business

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'post_count' => $postCount,
            'posts' => $posts,
            'is_following' => $isFollowing,
            'is_business' => $business
        ]);
    }
}
