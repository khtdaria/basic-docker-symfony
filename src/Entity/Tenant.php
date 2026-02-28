<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
#[ORM\Table(name: 'tenants')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'])]
class Tenant
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9\-]+$/', message: 'Slug may only contain lowercase letters, digits and hyphens.')]
    private string $slug;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isSuperTenant = false;

    /** @var Collection<int, User> */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'tenant', cascade: ['persist'])]
    private Collection $users;

    /** @var Collection<int, Area> */
    #[ORM\OneToMany(targetEntity: Area::class, mappedBy: 'tenant', cascade: ['persist'])]
    private Collection $areas;

    /** @var Collection<int, VrDevice> */
    #[ORM\OneToMany(targetEntity: VrDevice::class, mappedBy: 'tenant', cascade: ['persist'])]
    private Collection $vrDevices;

    /** @var Collection<int, Video> */
    #[ORM\ManyToMany(targetEntity: Video::class, inversedBy: 'tenants')]
    #[ORM\JoinTable(name: 'tenant_videos')]
    private Collection $videos;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->areas = new ArrayCollection();
        $this->vrDevices = new ArrayCollection();
        $this->videos = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function isSuperTenant(): bool
    {
        return $this->isSuperTenant;
    }

    public function setIsSuperTenant(bool $isSuperTenant): static
    {
        $this->isSuperTenant = $isSuperTenant;

        return $this;
    }

    /** @return Collection<int, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /** @return Collection<int, Area> */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    /** @return Collection<int, VrDevice> */
    public function getVrDevices(): Collection
    {
        return $this->vrDevices;
    }

    /** @return Collection<int, Video> */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        $this->videos->removeElement($video);

        return $this;
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
        return $this->name;
    }
}
