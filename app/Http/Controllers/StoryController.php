<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class StoryController extends Controller
{
//    use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;

public function store(Request $request)
{
    $request->validate([
        'media' => 'required|file',
        'media_type' => 'required|string',
        'caption' => 'nullable|string',
        'music_url' => 'nullable|url',           // ✅ optional
        'music_title' => 'nullable|string',      // ✅ optional
    ]);

    $file = $request->file('media');
    $mediaType = $request->media_type;
    $userId = auth()->id();
    $filename = '';

    if ($mediaType === 'image') {
        $filename = 'stories/' . uniqid() . '.jpg';
        $manager = new ImageManager(new Driver());

        $image = $manager->read($file)
            ->resize(1080, null, fn ($constraint) => $constraint->aspectRatio()->upsize())
            ->encode(new \Intervention\Image\Encoders\JpegEncoder(quality: 55));

        Storage::disk('public')->put($filename, (string) $image);
    } else {
        $filename = $file->store('stories', 'public');
    }

    $story = Story::create([
        'user_id' => $userId,
        'media_url' => Storage::url($filename),
        'media_type' => $mediaType,
        'caption' => $request->caption,
        'expires_at' => now()->addHours(24),
        'music_url' => $request->music_url,         // ✅ Save music_url
        'music_title' => $request->music_title,     // ✅ Save music_title
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
