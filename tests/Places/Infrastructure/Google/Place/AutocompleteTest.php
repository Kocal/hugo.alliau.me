<?php

declare(strict_types=1);

namespace App\Tests\Places\Infrastructure\Google\Place;

use App\Places\Application\Command\CreatePlace;
use App\Places\Domain\Data\PlaceType;
use App\Places\Infrastructure\Google\Place\AddressComponent;
use App\Places\Infrastructure\Google\Place\Autocomplete;
use App\Places\Infrastructure\Google\Place\Geometry;
use App\Places\Infrastructure\Google\Place\Location;
use App\Tests\Places\Infrastructure\Google\Factory\AutocompleteFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Autocomplete::class)]
#[CoversClass(AddressComponent::class)]
#[CoversClass(Geometry::class)]
#[CoversClass(Location::class)]
#[CoversClass(CreatePlace::class)]
final class AutocompleteTest extends TestCase
{
    public function testConstruct(): void
    {
        $autocomplete = new Autocomplete(
            'McDonalds',
            'https://url...',
            '123 Main St, Springfield, IL 62701, USA',
            new Geometry(new Location(1.1234, -1.1234)),
            [
                new AddressComponent('United States of America', 'USA', ['country', 'political']),
                new AddressComponent('Illinois', 'IL', ['administrative_area_level_1', 'political']),
            ],
            'https://icon...',
            [PlaceType::AMERICAN_RESTAURANT],
        );

        $this->assertSame('McDonalds', $autocomplete->name);
        $this->assertSame('https://url...', $autocomplete->url);
        $this->assertSame('123 Main St, Springfield, IL 62701, USA', $autocomplete->formattedAddress);
        $this->assertEqualsWithDelta(1.1234, $autocomplete->geometry->location->lat, PHP_FLOAT_EPSILON);
        $this->assertSame(-1.1234, $autocomplete->geometry->location->lng);
        $this->assertCount(2, $autocomplete->addressComponents);
        $this->assertSame('United States of America', $autocomplete->addressComponents[0]->longName);
        $this->assertSame('USA', $autocomplete->addressComponents[0]->shortName);
        $this->assertSame(['country', 'political'], $autocomplete->addressComponents[0]->types);
        $this->assertSame('Illinois', $autocomplete->addressComponents[1]->longName);
        $this->assertSame('IL', $autocomplete->addressComponents[1]->shortName);
        $this->assertSame(['administrative_area_level_1', 'political'], $autocomplete->addressComponents[1]->types);
        $this->assertSame('https://icon...', $autocomplete->iconMaskBaseUri);
        $this->assertCount(1, $autocomplete->types);
        $this->assertSame(PlaceType::AMERICAN_RESTAURANT, $autocomplete->types[0]);
    }

    public function testToCreatePlace(): void
    {
        $createPlace = AutocompleteFactory::seoulTower()->toCreatePlace();

        $this->assertSame('N Seoul Tower', $createPlace->name);
        $this->assertSame([37.5511694, 126.9882266], $createPlace->coordinates);
        $this->assertSame('105 Namsangongwon-gil, Yongsan District, Seoul, Corée du Sud', $createPlace->formattedAddress);
        $this->assertSame('Corée du Sud', $createPlace->country);
        $this->assertSame('Seoul', $createPlace->city);
        $this->assertSame('https://maps.google.com/?cid=6699200636580889253', $createPlace->googleMapsUrl);
        $this->assertSame('https://maps.gstatic.com/mapfiles/place_api/icons/v2/generic_pinlet.svg', $createPlace->iconMaskUri);
        $this->assertSame([
            PlaceType::TOURIST_ATTRACTION,
            PlaceType::POINT_OF_INTEREST,
            PlaceType::ESTABLISHMENT,
        ], $createPlace->types);
    }

    public function testToCreatePlacePrefersLocalityAsCity(): void
    {
        $autocomplete = new Autocomplete(
            'Musée du Louvre',
            'https://url...',
            null,
            new Geometry(new Location(48.8606, 2.3376)),
            [
                new AddressComponent('Île-de-France', 'IDF', ['administrative_area_level_1', 'political']),
                new AddressComponent('Paris', 'Paris', ['locality', 'political']),
                new AddressComponent('France', 'FR', ['country', 'political']),
            ],
            'https://icon...',
            [PlaceType::MUSEUM],
        );

        $createPlace = $autocomplete->toCreatePlace();

        $this->assertSame('Paris', $createPlace->city);
        $this->assertSame('France', $createPlace->country);
        $this->assertNull($createPlace->formattedAddress);
    }
}
