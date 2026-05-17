<?php

namespace App\Console\Commands;

use App\Models\PostMedia;
use App\Services\PostMediaThumbnailService;
use Illuminate\Console\Command;

class GeneratePostMediaThumbnails extends Command
{
    protected $signature = 'post-media:generate-thumbnails
                            {--force : Regenerate thumbnails even when thumbnail_path is already set}
                            {--limit= : Maximum number of videos to process}';

    protected $description = 'Generate poster thumbnails for post videos (uses configured frame, default: 4th frame)';

    public function handle(PostMediaThumbnailService $thumbnailService): int
    {
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
                $this->warn("Failed: post_media #{$media->id} ({$media->file_path})");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Generated: {$success}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
