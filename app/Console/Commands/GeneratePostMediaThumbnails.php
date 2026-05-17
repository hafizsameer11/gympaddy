<?php

namespace App\Console\Commands;

use App\Models\PostMedia;
use App\Services\PostMediaThumbnailService;
use Illuminate\Console\Command;

class GeneratePostMediaThumbnails extends Command
{
    protected $signature = 'post-media:generate-thumbnails
                            {--force : Regenerate thumbnails even when thumbnail_path is already set}
                            {--limit= : Maximum number of videos to process}
                            {--debug : Show ffmpeg path and last error for failures}';

    protected $description = 'Generate poster thumbnails for post videos (frame #4 by default, or generic placeholder if ffmpeg fails)';

    public function handle(PostMediaThumbnailService $thumbnailService): int
    {
        $ffmpeg = $thumbnailService->ffmpegBinary();
        $ffprobe = $thumbnailService->ffprobeBinary();

        if (!$ffmpeg) {
            $this->error('ffmpeg not found. Install it (apt install ffmpeg) or set FFMPEG_PATH in .env');
            if (!config('media.video_thumbnail_placeholder_fallback', true)) {
                return self::FAILURE;
            }
            $this->warn('Will use generic placeholder images only (VIDEO_THUMBNAIL_PLACEHOLDER_FALLBACK=true).');
        } else {
            $this->info("Using ffmpeg: {$ffmpeg}");
            if ($ffprobe) {
                $this->info("Using ffprobe: {$ffprobe}");
            }
        }

        $query = PostMedia::query()->where('media_type', 'video');

        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('thumbnail_path')
                    ->orWhere('thumbnail_path', '');
            });
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $videos = $query->orderBy('id')->get();
        $total = $videos->count();

        if ($total === 0) {
            $this->info('No post videos need thumbnail generation.');

            return self::SUCCESS;
        }

        $frame = $thumbnailService->frameNumber();
        $this->info("Processing {$total} video(s) using frame #{$frame}…");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($videos as $media) {
            $path = $thumbnailService->ensureThumbnail($media, (bool) $this->option('force'));

            if ($path) {
                $success++;
            } else {
                $failed++;
                $this->newLine();
                $msg = "Failed: post_media #{$media->id} ({$media->file_path})";
                if ($this->option('debug') && $thumbnailService->getLastError()) {
                    $msg .= "\n  → " . $thumbnailService->getLastError();
                }
                $this->warn($msg);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Generated: {$success}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
