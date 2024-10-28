<?php

declare(strict_types=1);

namespace App\Places\Domain\Google\Place;

use App\Places\Domain\PlaceType;

final class Autocomplete
{
    /**
     * @param array<AddressComponent> $addressComponents
     * @param array<PlaceType> $types
     */
    public function __construct(
        public string $name,
        public string $url,
        public string|null $formattedAddress,
        public Geometry $geometry,
        public array $addressComponents,
        public string $iconMaskBaseUri,
        public array $types,
    ) {
    }
}
