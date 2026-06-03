<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\PostMediaProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreatePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 3600;

    /**
     * @param  array<int, array{disk_path: string, client_name: string, mime_type: string, order: int}>  $tempFiles
     */
    public function __construct(
        public readonly int $postId,
        public readonly array $tempFiles,
        public readonly ?string $tempDirectory = null,
    ) {
        $this->onQueue(config('media.post_creation_queue', 'default'));
    }

    public function handle(PostMediaProcessingService $processor): void
    {
        $post = Post::withoutGlobalScopes()->find($this->postId);

        if (!$post || $post->publish_status !== 'processing') {
            $this->cleanupTempUploads();

            return;
        }

        try {
            foreach ($this->tempFiles as $file) {
                $processor->attachFromTemp($post, $file);
            }

            $post->update(['publish_status' => 'published']);

            Log::info('Post published after background processing', ['post_id' => $post->id]);
        } catch (\Throwable $e) {
            Log::error('CreatePostJob failed', [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
            ]);

            $post->update(['publish_status' => 'failed']);
            $post->delete();

            throw $e;
        } finally {
            $this->cleanupTempUploads();
        }
    }

    public function failed(\Throwable $exception): void
    {
        Post::withoutGlobalScopes()
            ->where('id', $this->postId)
            ->where('publish_status', 'processing')
            ->update(['publish_status' => 'failed']);

        Post::withoutGlobalScopes()
            ->where('id', $this->postId)
            ->where('publish_status', 'failed')
            ->delete();

        $this->cleanupTempUploads();

        report($exception);
    }

    private function cleanupTempUploads(): void
    {
        foreach ($this->tempFiles as $file) {
            $path = $file['disk_path'] ?? null;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($this->tempDirectory && Storage::disk('public')->exists($this->tempDirectory)) {
            Storage::disk('public')->deleteDirectory($this->tempDirectory);
        }
    }
}
