<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
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
}
