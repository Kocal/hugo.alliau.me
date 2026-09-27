<?php

declare(strict_types=1);

namespace App\Places\Application\Command;

use App\Places\Domain\Data\PlaceType;

final readonly class CreatePlace
{
    /**
     * @param array{float, float} $coordinates
     * @param array<PlaceType> $types
     */
    public function __construct(
        public string $name,
        public array $coordinates,
        public string|null $formattedAddress,
        public string|null $country,
        public string|null $city,
        public string|null $googleMapsUrl,
        public string|null $iconMaskUri,
        public array $types,
    ) {
    }
}
