<?php

declare(strict_types=1);

namespace App\Tests\Places\Infrastructure\Double\Repository;

use App\Places\Domain\Data\Place;
use App\Places\Domain\Repository\PlaceRepository;

final class FakePlaceRepository implements PlaceRepository
{
    /**
     * @var list<Place>
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
        $places = $this->places;
        usort($places, fn (Place $a, Place $b): int => $b->getUpdatedAt() <=> $a->getUpdatedAt());

        return $places[0] ?? throw new \LogicException('No place has been added.');
    }

    #[\Override]
    public function findAll(): array
    {
        return $this->places;
    }
}
