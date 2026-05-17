namespace App\Services;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoProcessingService
{
    public function encodeToMp4($inputPath, $outputPath)
    {
        FFMpeg::fromDisk('videos')
            ->open($inputPath)
            ->export()
            ->toDisk('videos')
            ->inFormat(new \FFMpeg\Format\Video\X264)
            ->save($outputPath);
    }

    /**
     * @deprecated Use PostMediaThumbnailService for post media (frame-based, public disk).
     */
    public function generateThumbnail($inputPath, $thumbnailPath)
    {
        $frame = max(1, (int) config('media.video_thumbnail_frame', 4));
        $seconds = max(0.0, ($frame - 1) / 30);

        FFMpeg::fromDisk('videos')
            ->open($inputPath)
            ->getFrameFromSeconds($seconds)
            ->export()
            ->toDisk('thumbnails')
            ->save($thumbnailPath);
    }
}
