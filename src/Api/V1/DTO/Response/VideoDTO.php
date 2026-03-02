<?php

declare(strict_types=1);

namespace App\Api\V1\DTO\Response;

use App\Entity\Video;

final readonly class VideoDTO
{
    public string $id;
    public string $title;
    public ?string $description;
    public ?string $hlsUrl;
    public ?string $previewImageUrl;
    public ?int $durationSec;
    public ?string $codec;
    public string $status;

    public function __construct(Video $video, string $baseUrl)
    {
        $videoId = $video->getId()->toString();

        $this->id = $videoId;
        $this->title = $video->getTitle();
        $this->description = $video->getDescription();
        $this->durationSec = $video->getDurationSec();
        $this->codec = $video->getCodec();
        $this->status = $video->getStatus()->value;

        $storageBase = $baseUrl . '/storage/videos/' . $videoId;

        $this->hlsUrl = $video->getHlsPath() !== null
            ? $storageBase . '/' . $video->getHlsPath()
            : null;

        $this->previewImageUrl = $video->getPreviewImagePath() !== null
            ? $storageBase . '/' . $video->getPreviewImagePath()
            : null;
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'hlsUrl' => $this->hlsUrl,
            'previewImageUrl' => $this->previewImageUrl,
            'durationSec' => $this->durationSec,
            'codec' => $this->codec,
            'status' => $this->status,
        ];
    }
}
