<?php

namespace App\Console\Commands;

use App\Models\LiveStream;
use Illuminate\Console\Command;

class MarkStaleStreamsInactive extends Command
{
    protected $signature = 'livestreams:cleanup';
    protected $description = 'Mark live streams with stale heartbeats as inactive (stream host closed app)';

    public function handle(): void
    {
        // Streams are considered dead if:
        // 1. They have a heartbeat that is older than 2 minutes, OR
        // 2. They have no heartbeat at all and were last updated more than 2 minutes ago
        $threshold = now()->subMinutes(2);

        $count = LiveStream::where('is_active', true)
            ->where(function ($query) use ($threshold) {
                $query->where('last_heartbeat_at', '<', $threshold)
                      ->orWhere(function ($q) use ($threshold) {
                          $q->whereNull('last_heartbeat_at')
                            ->where('updated_at', '<', $threshold);
                      });
            })
            ->update(['is_active' => false]);

        if ($count > 0) {
            $this->info("Marked {$count} stale live stream(s) as inactive.");
        }
    }
}
