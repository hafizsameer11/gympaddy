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

    public function generateThumbnail($inputPath, $thumbnailPath)
    {
        FFMpeg::fromDisk('videos')
            ->open($inputPath)
            ->getFrameFromSeconds(1)
            ->export()
            ->toDisk('thumbnails')
            ->save($thumbnailPath);
    }
}
