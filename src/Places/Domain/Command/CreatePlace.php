<?php

declare(strict_types=1);

namespace App\Places\Domain\Command;

use App\Places\Domain\Google\Place\Autocomplete;
use App\Places\Domain\PlaceType;

final readonly class CreatePlace
{
    /**
     * @param array<PlaceType> $types
     */
    public function __construct(
        public CreateAddress $createAddress,
        public string|null $googleMapsUrl,
        public string|null $iconMaskUri,
        public array $types,
    ) {
    }

    public static function fromGoogleAutocomplete(Autocomplete $autocomplete): self
    {
        return new self(
            CreateAddress::fromGoogleAutocomplete($autocomplete),
            googleMapsUrl: $autocomplete->url,
            iconMaskUri: $autocomplete->iconMaskBaseUri . '.svg',
            types: $autocomplete->types,
        );
    }
}
