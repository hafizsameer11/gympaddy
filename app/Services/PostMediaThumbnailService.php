<?php

namespace App\Services;

use App\Models\PostMedia;
use FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostMediaThumbnailService
{
    /** Fallback seek time when FPS cannot be read (~4th frame @ 30fps). */
    private const FALLBACK_TIME_SECONDS = 0.133;

    public function frameNumber(): int
    {
        return max(1, (int) config('media.video_thumbnail_frame', 4));
    }

    /**
     * Extract a poster frame from a video on the public disk and return the relative thumbnail path.
     */
    public function generateForVideoPath(string $videoRelativePath, int $postId, int $order = 0): ?string
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($videoRelativePath)) {
            Log::warning('Video not found for thumbnail generation', ['path' => $videoRelativePath]);

            return null;
        }

        $fullVideoPath = $disk->path($videoRelativePath);
        $baseName = pathinfo($videoRelativePath, PATHINFO_FILENAME);
        $thumbFileName = "{$baseName}_thumb_{$order}.jpg";
        $thumbRelativePath = "posts/{$postId}/thumbnails/{$thumbFileName}";

        $disk->makeDirectory("posts/{$postId}/thumbnails");
        $fullThumbPath = $disk->path($thumbRelativePath);

        if ($this->extractFrameWithPhpFfmpeg($fullVideoPath, $fullThumbPath)) {
            return $thumbRelativePath;
        }

        if ($this->extractFrameWithShell($fullVideoPath, $fullThumbPath)) {
            return $thumbRelativePath;
        }

        Log::error('All thumbnail generation strategies failed', ['video' => $videoRelativePath]);

        return null;
    }

    public function ensureThumbnail(PostMedia $media, bool $force = false): ?string
    {
        if ($media->media_type !== 'video') {
            return null;
        }

        if (!$force && $media->thumbnail_path && Storage::disk('public')->exists($media->thumbnail_path)) {
            return $media->thumbnail_path;
        }

        if ($force) {
            $this->deleteThumbnail($media->thumbnail_path);
        }

        $thumbnailPath = $this->generateForVideoPath(
            $media->file_path,
            (int) $media->post_id,
            (int) $media->order
        );

        if ($thumbnailPath) {
            $media->forceFill(['thumbnail_path' => $thumbnailPath])->save();
        }

        return $thumbnailPath;
    }

    public function deleteThumbnail(?string $thumbnailPath): void
    {
        if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }
    }

    private function extractFrameWithPhpFfmpeg(string $fullVideoPath, string $fullThumbPath): bool
    {
        try {
            $seconds = $this->resolveSeekTimeSeconds($fullVideoPath);
            $ffmpeg = FFMpeg\FFMpeg::create($this->ffmpegConfig());
            $video = $ffmpeg->open($fullVideoPath);
            $frame = $video->frame(TimeCode::fromSeconds($seconds));
            $frame->save($fullThumbPath);

            return is_file($fullThumbPath);
        } catch (\Throwable $e) {
            Log::warning('PHP-FFMpeg thumbnail extraction failed', [
                'video' => $fullVideoPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Shell fallback: select exact frame index (0-based) with ffmpeg -vf select=eq(n\,N).
     */
    private function extractFrameWithShell(string $fullVideoPath, string $fullThumbPath): bool
    {
        $ffmpeg = config('media.ffmpeg_path', '/usr/bin/ffmpeg');
        $frameIndex = $this->frameNumber() - 1;

        $command = sprintf(
            '%s -y -i %s -vf %s -vframes 1 -q:v 2 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($fullVideoPath),
            escapeshellarg('select=eq(n\\,' . $frameIndex . ')'),
            escapeshellarg($fullThumbPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($fullThumbPath)) {
            Log::warning('Shell ffmpeg thumbnail extraction failed', [
                'video' => $fullVideoPath,
                'exit_code' => $exitCode,
                'output' => implode("\n", array_slice($output, -5)),
            ]);

            return false;
        }

        return true;
    }

    private function resolveSeekTimeSeconds(string $fullVideoPath): float
    {
        try {
            $ffprobe = FFMpeg\FFProbe::create($this->ffmpegConfig());
            $stream = $ffprobe->streams($fullVideoPath)->videos()->first();

            if (!$stream) {
                return self::FALLBACK_TIME_SECONDS;
            }

            $fps = $this->parseFrameRate(
                $stream->get('avg_frame_rate') ?? $stream->get('r_frame_rate')
            );

            if ($fps <= 0) {
                return self::FALLBACK_TIME_SECONDS;
            }

            $frameIndex = $this->frameNumber() - 1;

            return max(0.0, $frameIndex / $fps);
        } catch (\Throwable) {
            return self::FALLBACK_TIME_SECONDS;
        }
    }

    private function parseFrameRate(?string $rate): float
    {
        if (!$rate || $rate === '0/0') {
            return 30.0;
        }

        if (str_contains($rate, '/')) {
            [$num, $den] = array_map('floatval', explode('/', $rate, 2));

            return $den > 0 ? $num / $den : 30.0;
        }

        $parsed = (float) $rate;

        return $parsed > 0 ? $parsed : 30.0;
    }

    private function ffmpegConfig(): array
    {
        return [
            'ffmpeg.binaries' => config('media.ffmpeg_path', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => config('media.ffprobe_path', '/usr/bin/ffprobe'),
            'timeout' => 120,
        ];
    }
}
