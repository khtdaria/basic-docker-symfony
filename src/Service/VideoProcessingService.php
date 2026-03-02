<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Video;
use App\Enum\VideoStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final readonly class VideoProcessingService
{
    public function __construct(
        private string          $storageDir,
        private LoggerInterface $logger,
    ) {
    }

    public function getVideoDir(string $videoId): string
    {
        return $this->storageDir . '/videos/' . $videoId;
    }

    /**
     *
     * @throws ProcessFailedException|\RuntimeException
     */
    public function process(Video $video): void
    {
        $videoId = $video->getId()->toString();
        $videoDir = $this->getVideoDir($videoId);
        $originalPath = $videoDir . '/' . $video->getOriginalPath();
        $hlsDir = $videoDir . '/hls';
        $previewPath = $videoDir . '/preview.jpg';

        $this->logger->debug('VideoProcessingService: starting', [
            'videoId' => $videoId,
            'originalPath' => $originalPath,
            'originalExists' => file_exists($originalPath),
        ]);

        if (!file_exists($originalPath)) {
            throw new \RuntimeException(sprintf('Original file not found: %s', $originalPath));
        }

        if (!is_dir($hlsDir)) {
            mkdir($hlsDir, 0755, true);
        }

        $streamInfo = $this->probeAllStreams($originalPath);

        $this->logger->debug('VideoProcessingService: metadata extracted', [
            'videoId' => $videoId,
            'duration' => $streamInfo['duration'],
            'codec' => $streamInfo['codec'],
            'height' => $streamInfo['height'],
            'audioCodec' => $streamInfo['audioCodec'],
        ]);

        $this->generatePreview($originalPath, $previewPath);

        $this->transcodeToHls($originalPath, $hlsDir . '/playlist.m3u8', $streamInfo);

        $video->setHlsPath('hls/playlist.m3u8');
        $video->setPreviewImagePath('preview.jpg');
        $video->setDurationSec($streamInfo['duration']);
        $video->setCodec($streamInfo['codec']);
        $video->setStatus(VideoStatus::Ready);
    }

    /**
     * @param array{codec: ?string, height: int, audioCodec: ?string, duration: int} $streamInfo
     */
    private function transcodeToHls(string $inputPath, string $outputPlaylist, array $streamInfo): void
    {
        $canCopyVideo = $streamInfo['codec'] === 'h264' && $streamInfo['height'] <= 1080;
        $canCopyAudio = $streamInfo['audioCodec'] === 'aac';

        $this->logger->info('VideoProcessingService: starting HLS transcoding', [
            'input' => $inputPath,
            'canCopyVideo' => $canCopyVideo,
            'canCopyAudio' => $canCopyAudio,
        ]);

        $cmd = ['ffmpeg', '-y', '-i', $inputPath];

        if ($canCopyVideo) {
            $cmd = array_merge($cmd, ['-c:v', 'copy']);
        } else {
            $cmd = array_merge($cmd, [
                '-vf', 'scale=-2:min(ih\,1080)',
                '-c:v', 'libx264',
                '-preset', 'veryfast',
                '-crf', '23',
            ]);
        }

        if ($canCopyAudio) {
            $cmd = array_merge($cmd, ['-c:a', 'copy']);
        } else {
            $cmd = array_merge($cmd, ['-c:a', 'aac', '-b:a', '128k']);
        }

        $cmd = array_merge($cmd, [
            '-start_number', '0',
            '-hls_time', '10',
            '-hls_list_size', '0',
            '-f', 'hls',
            $outputPlaylist,
        ]);

        $process = new Process($cmd);
        $process->setTimeout(3600);

        try {
            $process->mustRun();
            $this->logger->info('VideoProcessingService: HLS transcoding done', ['output' => $outputPlaylist]);
        } catch (ProcessFailedException $e) {
            $this->logger->error('VideoProcessingService: HLS transcoding failed', [
                'input' => $inputPath,
                'exitCode' => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
            ]);
            throw $e;
        }
    }

    private function generatePreview(string $inputPath, string $outputPath): void
    {
        $this->logger->info('VideoProcessingService: generating preview', ['input' => $inputPath]);

        $process = new Process([
            'ffmpeg', '-y',
            '-i', $inputPath,
            '-ss', '00:00:03',
            '-vframes', '1',
            '-q:v', '2',
            $outputPath,
        ]);
        $process->setTimeout(120);

        try {
            $process->mustRun();
            $this->logger->info('VideoProcessingService: preview generated', ['output' => $outputPath]);
        } catch (ProcessFailedException $e) {
            $this->logger->error('VideoProcessingService: preview generation failed', [
                'input' => $inputPath,
                'exitCode' => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
            ]);
            throw $e;
        }
    }

    /**
     * @return array{codec: ?string, height: int, audioCodec: ?string, duration: int}
     */
    private function probeAllStreams(string $inputPath): array
    {
        $process = new Process([
            'ffprobe',
            '-v', 'quiet',
            '-print_format', 'json',
            '-show_streams',
            '-show_format',
            $inputPath,
        ]);
        $process->setTimeout(60);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $this->logger->error('VideoProcessingService: ffprobe failed', [
                'input' => $inputPath,
                'exitCode' => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
            ]);
            throw $e;
        }

        /** @var array{format?: array{duration?: string}, streams?: list<array{codec_type?: string, codec_name?: string, height?: int}>} $data */
        $data = json_decode($process->getOutput(), true) ?? [];

        $duration = (int) round((float) ($data['format']['duration'] ?? 0));
        $codec = null;
        $height = 0;
        $audioCodec = null;

        foreach ($data['streams'] ?? [] as $stream) {
            if (($stream['codec_type'] ?? '') === 'video' && $codec === null) {
                $codec = $stream['codec_name'] ?? null;
                $height = $stream['height'] ?? 0;
            }
            if (($stream['codec_type'] ?? '') === 'audio' && $audioCodec === null) {
                $audioCodec = $stream['codec_name'] ?? null;
            }
        }

        return ['codec' => $codec, 'height' => $height, 'audioCodec' => $audioCodec, 'duration' => $duration];
    }
}
