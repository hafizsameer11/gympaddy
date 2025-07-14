<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'media' => 'required|file',
            'media_type' => 'required',
            'caption' => 'nullable|string',
        ]);

        $path = $request->file('media')->store('stories', 'public');

        $story = Story::create([
            'user_id' => auth()->id(),
            'media_url' => Storage::url($path),
            'media_type' => $request->media_type,
            'caption' => $request->caption,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'message' => 'Story uploaded successfully',
            'story' => $story
        ], 201);
    }
    public function getStories()
    {
        $user = Auth::user();

        // All active stories
        $stories = Story::where('expires_at', '>', now())
            ->where('user_id', '!=', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        // My own active stories
        $myStories = Story::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'All active stories retrieved successfully',
            'stories' => $stories,
            'my_stories' => $myStories,
        ], 200);
    }


    public function viewStory($storyId)
    {
        $user = Auth::user();
        $story = Story::findOrFail($storyId);

        // Check if the story is still valid
        if ($story->expires_at < now()) {
            return response()->json(['message' => 'Story has expired'], 404);
        }

        // Record the view
        $storyView = $story->views()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'viewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Story viewed successfully',
            'story' => $story,
            'view' => $storyView,
        ]);
    }
}
