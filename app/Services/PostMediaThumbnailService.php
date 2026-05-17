<?php

namespace App\Services;

use App\Models\PostMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class PostMediaThumbnailService
{
    private const FALLBACK_TIME_SECONDS = 0.133;

    private ?string $lastError = null;

    public function frameNumber(): int
    {
        return max(1, (int) config('media.video_thumbnail_frame', 4));
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function ffmpegBinary(): ?string
    {
        $configured = config('media.ffmpeg_path');
        if ($configured && $this->isExecutable($configured)) {
            return $configured;
        }

        foreach (['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg'] as $candidate) {
            if ($this->isExecutable($candidate)) {
                return $candidate;
            }
        }

        $which = trim((string) shell_exec('command -v ffmpeg 2>/dev/null') ?: '');
        if ($which !== '' && $this->isExecutable($which)) {
            return $which;
        }

        return null;
    }

    public function ffprobeBinary(): ?string
    {
        $configured = config('media.ffprobe_path');
        if ($configured && $this->isExecutable($configured)) {
            return $configured;
        }

        foreach (['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe'] as $candidate) {
            if ($this->isExecutable($candidate)) {
                return $candidate;
            }
        }

        $which = trim((string) shell_exec('command -v ffprobe 2>/dev/null') ?: '');
        if ($which !== '' && $this->isExecutable($which)) {
            return $which;
        }

        return null;
    }

    /**
     * Extract a poster frame from a video on the public disk and return the relative thumbnail path.
     */
    public function generateForVideoPath(string $videoRelativePath, int $postId, int $order = 0): ?string
    {
        $this->lastError = null;

        $fullVideoPath = $this->resolveVideoFullPath($videoRelativePath);
        if (!$fullVideoPath) {
            $this->lastError = "Video file not found: {$videoRelativePath}";

            return $this->maybeCreatePlaceholder($videoRelativePath, $postId, $order);
        }

        $disk = Storage::disk('public');
        $baseName = pathinfo($videoRelativePath, PATHINFO_FILENAME);
        $thumbFileName = "{$baseName}_thumb_{$order}.jpg";
        $thumbRelativePath = "posts/{$postId}/thumbnails/{$thumbFileName}";

        $disk->makeDirectory("posts/{$postId}/thumbnails");
        $fullThumbPath = $disk->path($thumbRelativePath);

        if (is_file($fullThumbPath)) {
            @unlink($fullThumbPath);
        }

        $seekSeconds = $this->resolveSeekTimeSeconds($fullVideoPath);

        foreach ($this->extractionAttempts($fullVideoPath, $seekSeconds) as $attempt) {
            if ($this->runFfmpegToPath($attempt['command'], $fullThumbPath)) {
                return $thumbRelativePath;
            }
            if (is_file($fullThumbPath)) {
                @unlink($fullThumbPath);
            }
        }

        if (config('media.video_thumbnail_placeholder_fallback', true)) {
            $placeholder = $this->createPlaceholderImage($fullThumbPath);
            if ($placeholder) {
                Log::info('Using generic placeholder thumbnail', ['video' => $videoRelativePath]);

                return $thumbRelativePath;
            }
        }

        Log::error('All thumbnail generation strategies failed', [
            'video' => $videoRelativePath,
            'error' => $this->lastError,
        ]);

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

    /**
     * @return list<array{label: string, command: string}>
     */
    private function extractionAttempts(string $fullVideoPath, float $seekSeconds): array
    {
        $ffmpeg = $this->ffmpegBinary();
        if (!$ffmpeg) {
            $this->lastError = 'ffmpeg binary not found. Install ffmpeg or set FFMPEG_PATH in .env';

            return [];
        }

        $in = escapeshellarg($fullVideoPath);
        $attempts = [];

        foreach ([$seekSeconds, 0.5, 1.0, 0.05] as $idx => $ss) {
            $ssArg = escapeshellarg((string) max(0, $ss));
            $attempts[] = [
                'label' => "seek_{$idx}",
                'command' => "{$ffmpeg} -hide_banner -loglevel error -y -ss {$ssArg} -i {$in} -frames:v 1 -an -q:v 3",
            ];
        }

        $attempts[] = [
            'label' => 'thumbnail_filter',
            'command' => "{$ffmpeg} -hide_banner -loglevel error -y -i {$in} -vf thumbnail -frames:v 1 -an -q:v 3",
        ];

        $frameIndex = $this->frameNumber() - 1;
        $attempts[] = [
            'label' => 'select_frame',
            'command' => "{$ffmpeg} -hide_banner -loglevel error -y -i {$in} -vf " . escapeshellarg("select=eq(n\\,{$frameIndex})") . ' -frames:v 1 -an -q:v 3',
        ];

        return $attempts;
    }

    private function runFfmpegToPath(string $commandBase, string $fullThumbPath): bool
    {
        $command = $commandBase . ' ' . escapeshellarg($fullThumbPath) . ' 2>&1';
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !$this->isValidImage($fullThumbPath)) {
            $this->lastError = trim(implode("\n", array_slice($output, -8))) ?: "ffmpeg exit code {$exitCode}";

            return false;
        }

        return true;
    }

    private function resolveVideoFullPath(string $relativePath): ?string
    {
        $relativePath = ltrim($relativePath, '/');
        $candidates = [
            Storage::disk('public')->path($relativePath),
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),
        ];

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path) && filesize($path) > 0) {
                return $path;
            }
        }

        return null;
    }

    private function resolveSeekTimeSeconds(string $fullVideoPath): float
    {
        $ffprobe = $this->ffprobeBinary();
        if (!$ffprobe) {
            return self::FALLBACK_TIME_SECONDS;
        }

        $command = sprintf(
            '%s -v error -select_streams v:0 -show_entries stream=avg_frame_rate -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
            escapeshellarg($ffprobe),
            escapeshellarg($fullVideoPath)
        );

        exec($command, $output, $exitCode);
        if ($exitCode !== 0 || empty($output[0])) {
            return self::FALLBACK_TIME_SECONDS;
        }

        $fps = $this->parseFrameRate(trim($output[0]));
        if ($fps <= 0) {
            return self::FALLBACK_TIME_SECONDS;
        }

        $frameIndex = $this->frameNumber() - 1;

        return max(0.05, $frameIndex / $fps);
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

    private function maybeCreatePlaceholder(string $videoRelativePath, int $postId, int $order): ?string
    {
        if (!config('media.video_thumbnail_placeholder_fallback', true)) {
            return null;
        }

        $disk = Storage::disk('public');
        $baseName = pathinfo($videoRelativePath, PATHINFO_FILENAME);
        $thumbRelativePath = "posts/{$postId}/thumbnails/{$baseName}_thumb_{$order}.jpg";
        $disk->makeDirectory("posts/{$postId}/thumbnails");
        $fullThumbPath = $disk->path($thumbRelativePath);

        return $this->createPlaceholderImage($fullThumbPath) ? $thumbRelativePath : null;
    }

    private function createPlaceholderImage(string $fullThumbPath): bool
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->lastError = 'GD extension not available for placeholder thumbnails';

            return false;
        }

        try {
            $width = (int) config('media.video_thumbnail_width', 720);
            $height = (int) config('media.video_thumbnail_height', 720);

            $img = imagecreatetruecolor($width, $height);
            if ($img === false) {
                return false;
            }

            $bg = imagecolorallocate($img, 26, 26, 26);
            $accent = imagecolorallocate($img, 148, 3, 4);
            imagefill($img, 0, 0, $bg);

            $triangleSize = (int) min($width, $height) * 0.12;
            $cx = (int) ($width / 2);
            $cy = (int) ($height / 2);
            $points = [
                $cx - (int) ($triangleSize * 0.4), $cy - (int) ($triangleSize / 2),
                $cx - (int) ($triangleSize * 0.4), $cy + (int) ($triangleSize / 2),
                $cx + (int) ($triangleSize * 0.6), $cy,
            ];
            imagefilledpolygon($img, $points, $accent);

            $dir = dirname($fullThumbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $ok = imagejpeg($img, $fullThumbPath, 82);
            imagedestroy($img);

            return $ok && $this->isValidImage($fullThumbPath);
        } catch (\Throwable $e) {
            $this->lastError = 'Placeholder failed: ' . $e->getMessage();

            return false;
        }
    }

    private function isValidImage(string $path): bool
    {
        if (!is_file($path) || filesize($path) < 128) {
            return false;
        }

        $info = @getimagesize($path);

        return $info !== false && in_array($info[2] ?? 0, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true);
    }

    private function isExecutable(string $path): bool
    {
        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        return is_executable($path) || (is_file($path) && is_readable($path));
    }
}
