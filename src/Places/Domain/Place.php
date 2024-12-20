<?php

declare(strict_types=1);

namespace App\Places\Domain;

use App\Shared\Domain\Data\ValueObject\PlaceId;
use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;

#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class Place implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'place_id')]
    private PlaceId $id;

    #[ORM\Embedded]
    private ?Address $address = null;

    /**
     * @var array<PlaceType>
     */
    #[ORM\Column(type: Types::JSON, enumType: PlaceType::class, options: [
        'jsonb' => true,
    ])]
    private array $types = [];

    #[ORM\Column]
    private string|null $googleMapsUrl = null;

    #[ORM\Column]
    private string|null $iconMaskUri = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = PlaceId::generate();
        $this->address = new Address();
        $this->createdAt = Clock::get()->now();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): PlaceId
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

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getEtag(): string
    {
        return 'places:place:' . $this->id . ':' . $this->updatedAt->format('U');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(Route::INDEX),
        ];
    }
}
