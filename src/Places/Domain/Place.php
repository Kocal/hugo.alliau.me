<?php

namespace App\Places\Domain;

use App\Places\Domain\Repository\PlaceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Embedded]
    private ?Address $address = null;

    /**
     * @var array<PlaceTag>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $tags = [];

    #[ORM\Column]
    private ?bool $toTry = null;

    #[ORM\Column]
    private string|null $googleMapsUrl = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column()]
    private PlaceOrigin $origin = PlaceOrigin::USER;

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

    public function setAddress(?Address $address): Place
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return array<PlaceTag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<PlaceTag> $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function isToTry(): ?bool
    {
        return $this->toTry;
    }

    public function setToTry(bool $toTry): static
    {
        $this->toTry = $toTry;

        return $this;
    }

    public function getOrigin(): PlaceOrigin
    {
        return $this->origin;
    }

    public function setOrigin(PlaceOrigin $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getGoogleMapsUrl(): ?string
    {
        return $this->googleMapsUrl;
    }

    public function setGoogleMapsUrl(?string $googleMapsUrl): Place
    {
        $this->googleMapsUrl = $googleMapsUrl;

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
}
