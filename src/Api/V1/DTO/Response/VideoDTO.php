<?php

declare(strict_types=1);

namespace App\Api\V1\DTO\Response;

use App\Entity\Video;

final readonly class VideoDTO
{
    public string $id;
    public string $title;
    public ?string $description;
    public string $url;
    public ?int $durationSeconds;
    public ?string $thumbnailUrl;

    public function __construct(Video $video)
    {
        $this->id = $video->getId()->toString();
        $this->title = $video->getTitle();
        $this->description = $video->getDescription();
        $this->url = $video->getUrl();
        $this->durationSeconds = $video->getDurationSeconds();
        $this->thumbnailUrl = $video->getThumbnailUrl();
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
            'url' => $this->url,
            'duration_seconds' => $this->durationSeconds,
            'thumbnail_url' => $this->thumbnailUrl,
        ];
    }
}
