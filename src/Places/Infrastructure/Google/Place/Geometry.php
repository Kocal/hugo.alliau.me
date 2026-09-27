<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Google\Place;

final readonly class Geometry
{
    public function __construct(
        public Location $location,
    ) {
    }
}
