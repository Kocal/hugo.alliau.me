<?php

namespace App\Places\Domain\Handler;

use App\Places\Domain\Address;
use App\Places\Domain\Place;
use App\Places\Domain\PlaceType;
use App\Shared\Google\Places\Client\GooglePlacesClient;
use App\Shared\Google\Places\GetAddressComponentFromPlaceResult;

final class CreatePlaceFromTextQuery
{
    public function __construct(
        private GooglePlacesClient $googlePlacesClient,
        private GetAddressComponentFromPlaceResult $getAddressComponentFromPlaceResult,
    ) {
    }

    public function __invoke(string $textQuery): Place
    {
        $results = $this->googlePlacesClient->textSearch($textQuery, [
            'places.displayName',
            'places.addressComponents',
            'places.googleMapsUri',
            'places.primaryType',
            'places.iconMaskBaseUri',
            'places.location',
            'places.formattedAddress',
        ]);

        $placeResult = $results['places'][0];

        $address = (new Address())
            ->setName($placeResult['displayName']['text'])
            ->setCountry(($this->getAddressComponentFromPlaceResult)($placeResult, 'country')['longText'] ?? null)
            ->setAdministrative(($this->getAddressComponentFromPlaceResult)($placeResult, 'administrative_area_level_1')['longText'] ?? null)
            ->setCounty(($this->getAddressComponentFromPlaceResult)($placeResult, 'administrative_area_level_2')['longText'] ?? null)
            ->setCity(($this->getAddressComponentFromPlaceResult)($placeResult, 'locality')['longText'] ?? null)
            ->setZipcode(($this->getAddressComponentFromPlaceResult)($placeResult, 'postal_code')['longText'] ?? null)
            ->setCoordinates([
                $placeResult['location']['latitude'],
                $placeResult['location']['longitude'],
            ]);

        if (null === $address->getCity()) {
            $address->setCity(($this->getAddressComponentFromPlaceResult)($placeResult, 'administrative_area_level_1')['longText'] ?? null);
        }

        if (null === $address->getCounty()) {
            $address->setCounty(($this->getAddressComponentFromPlaceResult)($placeResult, 'sublocality_level_1')['longText'] ?? null);
        }

        $place = (new Place())
            ->setGoogleMapsUrl($placeResult['googleMapsUri'])
            ->setIconMaskUri($placeResult['iconMaskBaseUri'].'.svg')
            ->setTypes([
                PlaceType::from($placeResult['primaryType'])
            ])
            ->setAddress($address)
        ;

        return $place;
    }
}