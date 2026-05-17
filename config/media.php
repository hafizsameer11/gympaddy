<?php

return [
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    /**
     * 1-based frame index used for post video thumbnails (4 = fourth frame).
     * Set to 3 for the third frame.
     */
    'video_thumbnail_frame' => (int) env('VIDEO_THUMBNAIL_FRAME', 4),

    /** When ffmpeg cannot read a video, save a generic poster image instead of failing. */
    'video_thumbnail_placeholder_fallback' => env('VIDEO_THUMBNAIL_PLACEHOLDER_FALLBACK', true),

    'video_thumbnail_width' => (int) env('VIDEO_THUMBNAIL_WIDTH', 720),
    'video_thumbnail_height' => (int) env('VIDEO_THUMBNAIL_HEIGHT', 720),
];

