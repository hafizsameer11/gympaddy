<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class PostMediaProcessingService
{
    public function __construct(
        private readonly PostVideoProcessingService $videoProcessor,
        private readonly PostMediaThumbnailService $thumbnailService
    ) {
    }

    /**
     * @param  array{disk_path: string, client_name: string, mime_type: string, order: int}  $file
     */
    public function attachFromTemp(Post $post, array $file): void
    {
        $diskPath = $file['disk_path'];
        $fullPath = Storage::disk('public')->path($diskPath);

        if (!is_file($fullPath)) {
            throw new \RuntimeException("Temp upload missing: {$diskPath}");
        }

        $mime = $file['mime_type'] ?? mime_content_type($fullPath) ?: 'application/octet-stream';
        $order = (int) ($file['order'] ?? 0);
        $originalName = $file['client_name'] ?? basename($diskPath);

        if (str_starts_with($mime, 'image/')) {
            $this->attachImageFromPath($post, $fullPath, $originalName, $mime, $order);
        } elseif (str_starts_with($mime, 'video/')) {
            $this->attachVideoFromPath($post, $fullPath, $originalName, $mime, $order);
        } else {
            throw new \RuntimeException("Unsupported media type: {$mime}");
        }
    }

    /** Sync path for post updates (existing behaviour). */
    public function attachFromUpload(Post $post, UploadedFile $file, int $order): void
    {
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $this->attachImageFromUpload($post, $file, $order);
        } else {
            $this->attachVideoFromUpload($post, $file, $order);
        }
    }

    private function attachImageFromUpload(Post $post, UploadedFile $file, int $order): void
    {
        $fileName = $this->buildFileName($file->getClientOriginalName(), $order, true);
        $filePath = "posts/{$post->id}/{$fileName}";
        Storage::disk('public')->makeDirectory("posts/{$post->id}");

        $manager = new ImageManager(new Driver());
        $image = $manager
            ->read($file)
            ->scale(width: 1080)
            ->encode(new JpegEncoder(quality: 55));

        Storage::disk('public')->put($filePath, (string) $image);

        PostMedia::create([
            'post_id' => $post->id,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'thumbnail_path' => null,
            'media_type' => 'image',
            'mime_type' => $file->getMimeType(),
            'file_size' => Storage::disk('public')->size($filePath),
            'order' => $order,
            'processing_status' => 'ready',
        ]);
    }

    private function attachImageFromPath(Post $post, string $fullPath, string $originalName, string $mime, int $order): void
    {
        $fileName = $this->buildFileName($originalName, $order, true);
        $filePath = "posts/{$post->id}/{$fileName}";
        Storage::disk('public')->makeDirectory("posts/{$post->id}");

        $manager = new ImageManager(new Driver());
        $image = $manager
            ->read($fullPath)
            ->scale(width: 1080)
            ->encode(new JpegEncoder(quality: 55));

        Storage::disk('public')->put($filePath, (string) $image);

        PostMedia::create([
            'post_id' => $post->id,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'thumbnail_path' => null,
            'media_type' => 'image',
            'mime_type' => $mime,
            'file_size' => Storage::disk('public')->size($filePath),
            'order' => $order,
            'processing_status' => 'ready',
        ]);
    }

    private function attachVideoFromUpload(Post $post, UploadedFile $file, int $order): void
    {
        $fileName = $this->buildFileName($file->getClientOriginalName(), $order, false, $file->getClientOriginalExtension());
        Storage::disk('public')->makeDirectory("posts/{$post->id}");
        $file->storeAs("posts/{$post->id}", $fileName, 'public');
        $storedPath = "posts/{$post->id}/{$fileName}";

        $media = PostMedia::create([
            'post_id' => $post->id,
            'file_path' => $storedPath,
            'file_name' => $fileName,
            'thumbnail_path' => null,
            'media_type' => 'video',
            'mime_type' => $file->getMimeType(),
            'file_size' => Storage::disk('public')->size($storedPath),
            'order' => $order,
            'processing_status' => 'pending',
        ]);

        $this->videoProcessor->process($media);
    }

    private function attachVideoFromPath(Post $post, string $fullPath, string $originalName, string $mime, int $order): void
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'mp4';
        $fileName = $this->buildFileName($originalName, $order, false, $ext);
        Storage::disk('public')->makeDirectory("posts/{$post->id}");
        $storedPath = "posts/{$post->id}/{$fileName}";
        Storage::disk('public')->put($storedPath, (string) file_get_contents($fullPath));

        $media = PostMedia::create([
            'post_id' => $post->id,
            'file_path' => $storedPath,
            'file_name' => $fileName,
            'thumbnail_path' => null,
            'media_type' => 'video',
            'mime_type' => $mime,
            'file_size' => Storage::disk('public')->size($storedPath),
            'order' => $order,
            'processing_status' => 'pending',
        ]);

        $this->videoProcessor->process($media);
    }

    private function buildFileName(string $originalName, int $order, bool $isImage, ?string $ext = null): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);

        return time() . '_' . $order . '_' . str()->slug($base)
            . ($isImage ? '.jpg' : '.' . ($ext ?: 'mp4'));
    }
}
