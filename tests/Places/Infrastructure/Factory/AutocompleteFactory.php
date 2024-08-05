<?php

declare(strict_types=1);

namespace App\Tests\Places\Infrastructure\Factory;

use App\Places\Domain\Google\Place\AddressComponent;
use App\Places\Domain\Google\Place\Autocomplete;
use App\Places\Domain\Google\Place\Geometry;
use App\Places\Domain\Google\Place\Location;
use App\Places\Domain\PlaceType;

final readonly class AutocompleteFactory
{
    public static function seoulTower(): Autocomplete
    {
        return new Autocomplete(
            name: 'N Seoul Tower',
            url: 'https://maps.google.com/?cid=6699200636580889253',
            formattedAddress: '105 Namsangongwon-gil, Yongsan District, Seoul, Corée du Sud',
            geometry: new Geometry(
                location: new Location(lat: 37.5511694, lng: 126.9882266),
            ),
            addressComponents: [
                new AddressComponent(longName: '105', shortName: '105', types: ['premise']),
                new AddressComponent(longName: 'Namsangongwon-gil', shortName: 'Namsangongwon-gil', types: ['sublocality_level_4', 'sublocality', 'political']),
                new AddressComponent(longName: 'Yongsan District', shortName: 'Yongsan District', types: ['sublocality_level_1', 'sublocality', 'political']),
                new AddressComponent(longName: 'Seoul', shortName: 'Seoul', types: ['administrative_area_level_1', 'political']),
                new AddressComponent(longName: 'Corée du Sud', shortName: 'KR', types: ['country', 'political']),
            ],
            iconMaskBaseUri: 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/generic_pinlet',
            types: [
                PlaceType::TOURIST_ATTRACTION,
                PlaceType::POINT_OF_INTEREST,
                PlaceType::ESTABLISHMENT,
            ]
        );
    }
}
