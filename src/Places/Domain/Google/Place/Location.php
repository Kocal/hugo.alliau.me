<?php

declare(strict_types=1);

namespace App\Places\Domain\Google\Place;

final readonly class Location
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }
}
