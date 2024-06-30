<?php
declare(strict_types=1);

namespace App\Tests\Forum\Infrastructure\Double\Repository;

use App\Places\Domain\Place;
use App\Places\Domain\Repository\PlaceRepository;

final class FakePlaceRepository implements PlaceRepository
{
    /**
     * @var array<Place> 
     */
    private array $places = [];
    
    public function add(Place $place): void
    {
        $this->places[] = $place;
    }

    public function getOneLatestUpdated(): Place
    {
        $max = max(array_map(fn(Place $place) => $place->getUpdatedAt(), $this->places));
        
        return array_filter($this->places, fn(Place $place) => $place->getUpdatedAt() === $max)[0];
    }

    public function findAll(): array
    {
        return $this->places;
    }
}
