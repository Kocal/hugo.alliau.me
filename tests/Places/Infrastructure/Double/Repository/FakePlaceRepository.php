<?php

declare(strict_types=1);

namespace App\Tests\Places\Infrastructure\Double\Repository;

use App\Places\Domain\Data\Place;
use App\Places\Domain\Repository\PlaceRepository;

final class FakePlaceRepository implements PlaceRepository
{
    /**
     * @var array<Place>
     */
    private array $places = [];

    #[\Override]
    public function add(Place $place): void
    {
        $this->places[] = $place;
    }

    #[\Override]
    public function getOneLatestUpdated(): Place
    {
        $max = max(array_map(fn (Place $place): ?\DateTimeImmutable => $place->getUpdatedAt(), $this->places));

        return array_filter($this->places, fn (Place $place): bool => $place->getUpdatedAt() === $max)[0];
    }

    #[\Override]
    public function findAll(): array
    {
        return $this->places;
    }
}
