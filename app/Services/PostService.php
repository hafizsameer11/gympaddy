<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
    use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Illuminate\Support\Facades\Storage;
class PostService
{
public function index()
{
    $perPage = request()->get('limit', 4); // default to 4 instead of 20

    return Post::with(['user', 'comments', 'likes.user', 'media'])
        ->withCount('allComments')
        ->orderByDesc('created_at')
        ->paginate($perPage);
}




    public function store($user, $validated)
    {
        $post = Post::create([
            'user_id' => $user->id,
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'] ?? null,
        ]);

        // Handle media uploads if present
        if (isset($validated['media']) && !empty($validated['media'])) {
            $this->handleMediaUploads($post, $validated['media']);
        }

        return response()->json($post->load(['user', 'comments', 'likes', 'media']), 201);
    }

    public function show($user, Post $post)
    {
        // if ($post->user_id !== $user->id) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }
        return $post->load(['user', 'comments', 'likes','media'])->loadCount('allComments');
    }

    public function update($user, Post $post, $validated)
    {
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $post->update($validated);
        return response()->json($post->load(['user', 'comments', 'likes']));
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



private function handleMediaUploads(Post $post, array $mediaFiles)
{
    $order = 0;
    $manager = new ImageManager(new Driver());

    foreach ($mediaFiles as $file) {
        $isImage = str_starts_with($file->getMimeType(), 'image/');
        $mediaType = $isImage ? 'image' : 'video';

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = time() . '_' . $order . '_' . str()->slug($originalName) . ($isImage ? '.jpg' : '.' . $file->getClientOriginalExtension());
        $filePath = "posts/{$post->id}/{$fileName}";

        // Ensure the post directory exists
        Storage::disk('public')->makeDirectory("posts/{$post->id}");

        if ($isImage) {
            // ✅ Compress image using Intervention v3
            $image = $manager
                ->read($file)
                ->scale(width: 1080) // resize proportionally
                ->encode(new JpegEncoder(quality: 55)); // compress

            Storage::disk('public')->put($filePath, (string) $image);
            $finalPath = $filePath;
            $fileSize = Storage::disk('public')->size($finalPath);
        } else {
            // ⏯️ Handle video upload/compression
            $tempPath = $file->storeAs('temp', $fileName, 'public');
            $finalPath = $this->compressVideo($tempPath, $filePath);
            $fileSize = Storage::disk('public')->size($finalPath);

            // Clean up temp file
            Storage::disk('public')->delete($tempPath);
        }

        // Save media record
        PostMedia::create([
            'post_id'    => $post->id,
            'file_path'  => $finalPath,
            'file_name'  => $fileName,
            'media_type' => $mediaType,
            'mime_type'  => $file->getMimeType(),
            'file_size'  => $fileSize,
            'order'      => $order,
        ]);

        $order++;
    }
}


    private function compressVideo(string $inputPath, string $outputPath): string
    {
        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => config('media.ffmpeg_path', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => config('media.ffprobe_path', '/usr/bin/ffprobe'),
                'timeout' => 3600,
                'ffmpeg.threads' => 12,
            ]);

            $inputFullPath = Storage::disk('public')->path($inputPath);
            $outputFullPath = Storage::disk('public')->path($outputPath);

            // Ensure output directory exists
            $outputDir = dirname($outputFullPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $video = $ffmpeg->open($inputFullPath);

            // Configure compression settings
            $format = new X264();
            $format->setKiloBitrate(1000) // 1000 kbps
                ->setAudioChannels(2)
                ->setAudioKiloBitrate(128);

            // Resize video if needed (max 720p)
            $video->filters()
                ->resize(new \FFMpeg\Coordinate\Dimension(1280, 720), \FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET);

            $video->save($format, $outputFullPath);

            Log::info("Video compressed successfully", [
                'input' => $inputPath,
                'output' => $outputPath
            ]);

            return $outputPath;
        } catch (\Exception $e) {
            Log::error("Video compression failed", [
                'input' => $inputPath,
                'output' => $outputPath,
                'error' => $e->getMessage()
            ]);

            // Fallback: copy original file
            Storage::disk('public')->copy($inputPath, $outputPath);
            return $outputPath;
        }
    }
}
