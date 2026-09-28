<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Google\Place;

use App\Places\Application\Command\CreatePlace;
use App\Places\Domain\Data\PlaceType;

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

    public function toCreatePlace(): CreatePlace
    {
        $country = null;
        foreach ($this->addressComponents as $addressComponent) {
            if (in_array('country', $addressComponent->types, true)) {
                $country = $addressComponent->longName;
                break;
            }
        }

        $city = null;
        foreach ($this->addressComponents as $addressComponent) {
            if (in_array('locality', $addressComponent->types, true)) {
                $city = $addressComponent->longName;
                break;
            }
        }

        if ($city === null) {
            foreach ($this->addressComponents as $addressComponent) {
                if (in_array('administrative_area_level_1', $addressComponent->types, true)) {
                    $city = $addressComponent->longName;
                    break;
                }
            }
        }

        return new CreatePlace(
            name: $this->name,
            coordinates: [
                $this->geometry->location->lat,
                $this->geometry->location->lng,
            ],
            formattedAddress: $this->formattedAddress,
            country: $country,
            city: $city,
            googleMapsUrl: $this->url,
            iconMaskUri: $this->iconMaskBaseUri . '.svg',
            types: $this->types,
        );
    }
}
