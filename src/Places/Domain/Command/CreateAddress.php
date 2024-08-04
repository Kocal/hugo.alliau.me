<?php

declare(strict_types=1);

namespace App\Places\Domain\Command;

use App\Places\Domain\Google\Place\Autocomplete;

final readonly class CreateAddress
{
    /**
     * @param array{float, float} $coordinates
     */
    public function __construct(
        public string $name,
        public array $coordinates,
        public string|null $formattedAddress,
        public string|null $country,
        public string|null $city,
    ) {
    }

    public static function fromGoogleAutocomplete(Autocomplete $autocomplete): self
    {
        $country = null;
        foreach ($autocomplete->addressComponents as $addressComponent) {
            if (in_array('country', $addressComponent->types, true)) {
                $country = $addressComponent->longName;
                break;
            }
        }

        $city = null;
        foreach ($autocomplete->addressComponents as $addressComponent) {
            if (in_array('locality', $addressComponent->types, true)) {
                $city = $addressComponent->longName;
                break;
            }
        }

        if ($city === null) {
            foreach ($autocomplete->addressComponents as $addressComponent) {
                if (in_array('administrative_area_level_1', $addressComponent->types, true)) {
                    $city = $addressComponent->longName;
                    break;
                }
            }
        }

        return new self(
            name: $autocomplete->name,
            coordinates: [
                $autocomplete->geometry->location->lat,
                $autocomplete->geometry->location->lng,
            ],
            formattedAddress: $autocomplete->formattedAddress,
            country: $country,
            city: $city,
        );
    }
}
