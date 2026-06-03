<?php

namespace App\Services;

use App\Models\PostMedia;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostVideoProcessingService
{
    public function __construct(
        private readonly PostMediaThumbnailService $thumbnailService
    ) {
    }

    public function process(PostMedia $media): void
    {
        if ($media->media_type !== 'video') {
            return;
        }

        $media->update(['processing_status' => 'processing']);

        $postId = (int) $media->post_id;
        $order = (int) $media->order;
        $inputPath = $media->file_path;

        $slug = pathinfo($media->file_name, PATHINFO_FILENAME) ?: 'video';
        $outputFileName = time() . '_' . $order . '_' . str()->slug($slug) . '.mp4';
        $outputPath = "posts/{$postId}/{$outputFileName}";

        Storage::disk('public')->makeDirectory("posts/{$postId}");

        try {
            $finalPath = $this->compressVideo($inputPath, $outputPath);
            $fileSize = Storage::disk('public')->size($finalPath);

            if ($finalPath !== $inputPath && Storage::disk('public')->exists($inputPath)) {
                Storage::disk('public')->delete($inputPath);
            }

            $thumbnailPath = $this->thumbnailService->generateForVideoPath(
                $finalPath,
                $postId,
                $order
            );

            $media->update([
                'file_path' => $finalPath,
                'file_size' => $fileSize,
                'thumbnail_path' => $thumbnailPath,
                'processing_status' => 'ready',
            ]);

            Log::info('Post video processed', [
                'post_media_id' => $media->id,
                'post_id' => $postId,
                'path' => $finalPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Post video processing failed', [
                'post_media_id' => $media->id,
                'post_id' => $postId,
                'input' => $inputPath,
                'error' => $e->getMessage(),
            ]);

            $media->update(['processing_status' => 'failed']);

            throw $e;
        }
    }

    public function compressVideo(string $inputPath, string $outputPath): string
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

            $outputDir = dirname($outputFullPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $video = $ffmpeg->open($inputFullPath);

            $format = new X264();
            $format->setKiloBitrate(1000)
                ->setAudioChannels(2)
                ->setAudioKiloBitrate(128);

            $video->filters()
                ->resize(new Dimension(1280, 720), ResizeFilter::RESIZEMODE_INSET);

            $video->save($format, $outputFullPath);

            return $outputPath;
        } catch (\Exception $e) {
            Log::warning('Video compression failed, keeping original file', [
                'input' => $inputPath,
                'output' => $outputPath,
                'error' => $e->getMessage(),
            ]);

            if (!Storage::disk('public')->exists($outputPath)) {
                Storage::disk('public')->copy($inputPath, $outputPath);
            }

            return Storage::disk('public')->exists($outputPath) ? $outputPath : $inputPath;
        }
    }
}
