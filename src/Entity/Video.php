<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\VideoStatus;
use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: 'videos')]
#[ORM\HasLifecycleCallbacks]
class Video
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: VideoStatus::class)]
    private VideoStatus $status = VideoStatus::Pending;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $originalPath = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $hlsPath = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $previewImagePath = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationSec = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $codec = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $processingError = null;

    #[ORM\Column]
    private bool $isActive = true;

    /** @var Collection<int, Tenant> */
    #[ORM\ManyToMany(targetEntity: Tenant::class, mappedBy: 'videos')]
    private Collection $tenants;

    /** @var Collection<int, VrDevice> */
    #[ORM\ManyToMany(targetEntity: VrDevice::class, mappedBy: 'videos')]
    private Collection $vrDevices;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->tenants = new ArrayCollection();
        $this->vrDevices = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): VideoStatus
    {
        return $this->status;
    }

    public function setStatus(VideoStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOriginalPath(): ?string
    {
        return $this->originalPath;
    }

    public function setOriginalPath(?string $originalPath): static
    {
        $this->originalPath = $originalPath;

        return $this;
    }

    public function getHlsPath(): ?string
    {
        return $this->hlsPath;
    }

    public function setHlsPath(?string $hlsPath): static
    {
        $this->hlsPath = $hlsPath;

        return $this;
    }

    public function getPreviewImagePath(): ?string
    {
        return $this->previewImagePath;
    }

    public function setPreviewImagePath(?string $previewImagePath): static
    {
        $this->previewImagePath = $previewImagePath;

        return $this;
    }

    public function getDurationSec(): ?int
    {
        return $this->durationSec;
    }

    public function setDurationSec(?int $durationSec): static
    {
        $this->durationSec = $durationSec;

        return $this;
    }

    public function getCodec(): ?string
    {
        return $this->codec;
    }

    public function setCodec(?string $codec): static
    {
        $this->codec = $codec;

        return $this;
    }

    public function getProcessingError(): ?string
    {
        return $this->processingError;
    }

    public function setProcessingError(?string $processingError): static
    {
        $this->processingError = $processingError;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /** @return Collection<int, Tenant> */
    public function getTenants(): Collection
    {
        return $this->tenants;
    }

    /** @return Collection<int, VrDevice> */
    public function getVrDevices(): Collection
    {
        return $this->vrDevices;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
