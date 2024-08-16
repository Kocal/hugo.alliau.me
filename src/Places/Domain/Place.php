<?php

declare(strict_types=1);

namespace App\Places\Domain;

use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class Place implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Embedded]
    private ?Address $address = null;

    /**
     * @var array<PlaceType>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ], enumType: PlaceType::class)]
    private array $types = [];

    #[ORM\Column]
    private string|null $googleMapsUrl = null;

    #[ORM\Column]
    private string|null $iconMaskUri = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->address = new Address();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return array<PlaceType>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array<PlaceType> $types
     */
    public function setTypes(array $types): static
    {
        $this->types = $types;

        return $this;
    }

    public function getGoogleMapsUrl(): ?string
    {
        return $this->googleMapsUrl;
    }

    public function setGoogleMapsUrl(?string $googleMapsUrl): self
    {
        $this->googleMapsUrl = $googleMapsUrl;

        return $this;
    }

    public function getIconMaskUri(): ?string
    {
        return $this->iconMaskUri;
    }

    public function setIconMaskUri(?string $iconMaskUri): self
    {
        $this->iconMaskUri = $iconMaskUri;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getEtag(): string
    {
        return 'places:place:' . $this->id . ':' . ($this->updatedAt?->format('U') ?? '0');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(Route::INDEX),
        ];
    }
}
