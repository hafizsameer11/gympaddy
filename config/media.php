<?php

return [
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    /**
     * 1-based frame index used for post video thumbnails (4 = fourth frame).
     * Set to 3 for the third frame.
     */
    'video_thumbnail_frame' => (int) env('VIDEO_THUMBNAIL_FRAME', 4),
];

