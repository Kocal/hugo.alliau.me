<?php

declare(strict_types=1);

namespace App\Places\Domain\Repository;

use App\Places\Domain\Data\Place;

interface PlaceRepository
{
    public function add(Place $place): void;

    public function getOneLatestUpdated(): Place;

    /**
     * @return list<Place>
     */
    public function findAll(): array;
}
