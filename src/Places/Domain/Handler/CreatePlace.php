<?php

namespace App\Places\Domain\Handler;

use App\Places\Domain\Address;
use App\Places\Domain\Place;
use App\Places\Domain\PlaceType;
use App\Shared\Google\Places\GetAddressComponent;

final class CreatePlace
{
    public function __construct(
        private GetAddressComponent $getAddressComponent,
    ) {
    }

    public function fromAutocomplete(array $autocomplete): Place
    {
        $address = (new Address())
            ->setName($autocomplete['name'])
            ->setFormattedAddress($autocomplete['formatted_address'] ?? null)
            ->setCountry(($this->getAddressComponent)($autocomplete['address_components'], 'country')['long_name'] ?? null)
            ->setCity(($this->getAddressComponent)($autocomplete['address_components'], 'locality')['long_name'] ?? null)
            ->setCoordinates([
                $autocomplete['geometry']['location']['lat'],
                $autocomplete['geometry']['location']['lng'],
            ]);

        if (null === $address->getCity()) {
            $address->setCity(($this->getAddressComponent)($autocomplete['address_components'], 'administrative_area_level_1')['long_name'] ?? null);
        }

        $place = (new Place())
            ->setGoogleMapsUrl($autocomplete['url'])
            ->setIconMaskUri($autocomplete['icon_mask_base_uri'].'.svg')
            ->setTypes(array_map(PlaceType::from(...), $autocomplete['types']))
            ->setAddress($address)
        ;

        return $place;
    }
}