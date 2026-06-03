<?php

namespace App\Services;

use App\Jobs\CreatePostJob;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private readonly PostMediaThumbnailService $thumbnailService,
        private readonly PostMediaProcessingService $mediaProcessor,
    ) {
    }

    public function index()
    {
        $perPage = request()->get('limit', 4);

        return Post::with(['user', 'comments', 'likes.user', 'media'])
            ->withCount(['allComments', 'shares as share_count'])
            ->where('is_hidden', false)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function store($user, $validated)
    {
        $mediaFiles = isset($validated['media']) ? array_values(array_filter($validated['media'])) : [];
        $hasMedia = $mediaFiles !== [];

        $post = Post::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'] ?? null,
            'publish_status' => $hasMedia ? 'processing' : 'published',
        ]);

        if ($hasMedia) {
            $batchId = Str::uuid()->toString();
            $tempDirectory = "post-uploads/{$user->id}/{$batchId}";
            $tempPayload = [];

            foreach ($mediaFiles as $order => $file) {
                $storedPath = $file->store($tempDirectory, 'public');
                $tempPayload[] = [
                    'disk_path' => $storedPath,
                    'client_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'order' => $order,
                ];
            }

            CreatePostJob::dispatch($post->id, $tempPayload, $tempDirectory)->afterResponse();
        }

        return response()->json(
            $this->formatPostResponse($post->load(['user', 'comments', 'likes', 'media'])),
            201
        );
    }

    public function show($user, Post $post)
    {
        if ($post->publish_status !== 'published' && (int) $post->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        return $this->formatPostResponse(
            $post->load(['user', 'comments', 'likes', 'media'])->loadCount(['allComments', 'shares as share_count'])
        );
    }

    public function update($user, Post $post, $validated)
    {
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $mediaInput = $validated['media'] ?? null;
        unset($validated['media']);

        $post->update(array_intersect_key($validated, array_flip(['title', 'content', 'media_url'])));

        if ($mediaInput !== null && $mediaInput !== []) {
            $files = is_array($mediaInput) ? $mediaInput : [$mediaInput];
            $files = array_values(array_filter($files));
            if ($files !== []) {
                foreach ($post->media as $existing) {
                    if ($existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                        Storage::disk('public')->delete($existing->file_path);
                    }
                    $this->thumbnailService->deleteThumbnail($existing->thumbnail_path);
                    $existing->delete();
                }
                foreach ($files as $order => $file) {
                    $this->mediaProcessor->attachFromUpload($post, $file, $order);
                }
            }
        }

        return response()->json(
            $this->formatPostResponse(
                $post->fresh()->load(['user', 'comments', 'likes', 'media'])->loadCount(['allComments', 'shares as share_count'])
            )
        );
    }

    public function destroy($user, Post $post)
    {
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        try {
            $post->delete();
            return response()->json(['message' => 'Post deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete post.', 'error' => $e->getMessage()], 500);
        }
    }

    /** Same JSON shape the mobile app already expects (no new fields). */
    private function formatPostResponse(Post $post): Post
    {
        $post->makeHidden(['publish_status']);

        return $post;
    }
}
