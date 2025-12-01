<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Command;

use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Command\CreatePlace;
use App\Places\Domain\Data\Google\Place\AddressComponent;
use App\Places\Domain\Data\Google\Place\Autocomplete;
use App\Places\Domain\Data\Google\Place\Geometry;
use App\Places\Domain\Data\Google\Place\Location;
use App\Places\Domain\Data\PlaceType;
use App\Tests\Places\Domain\Factory\AutocompleteFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreatePlace::class)]
#[CoversClass(CreateAddress::class)]
#[UsesClass(AddressComponent::class)]
#[UsesClass(Geometry::class)]
#[UsesClass(Location::class)]
#[CoversClass(Autocomplete::class)]
final class CreatePlaceTest extends TestCase
{
    public function testConstruct(): void
    {
        $createPlace = new CreatePlace(
            createAddress: $createAddress = new CreateAddress(
                name: 'name',
                coordinates: [41, 3],
                formattedAddress: 'formattedAddress',
                country: 'country',
                city: 'city',
            ),
            googleMapsUrl: 'googleMapsUrl',
            iconMaskUri: 'iconMaskUri',
            types: ['types'],
        );

        $this->assertSame($createAddress, $createPlace->createAddress);
        $this->assertSame('googleMapsUrl', $createPlace->googleMapsUrl);
        $this->assertSame('iconMaskUri', $createPlace->iconMaskUri);
        $this->assertSame(['types'], $createPlace->types);
    }

    public function testFromGoogleAutocomplete(): void
    {
        $createPlace = CreatePlace::fromGoogleAutocomplete(
            AutocompleteFactory::seoulTower()
        );

        $this->assertSame('N Seoul Tower', $createPlace->createAddress->name);
        $this->assertSame([37.5511694, 126.9882266], $createPlace->createAddress->coordinates);
        $this->assertSame('105 Namsangongwon-gil, Yongsan District, Seoul, Corée du Sud', $createPlace->createAddress->formattedAddress);
        $this->assertSame('Corée du Sud', $createPlace->createAddress->country);
        $this->assertSame('Seoul', $createPlace->createAddress->city);
        $this->assertSame('https://maps.google.com/?cid=6699200636580889253', $createPlace->googleMapsUrl);
        $this->assertSame('https://maps.gstatic.com/mapfiles/place_api/icons/v2/generic_pinlet.svg', $createPlace->iconMaskUri);
        $this->assertSame([
            PlaceType::TOURIST_ATTRACTION,
            PlaceType::POINT_OF_INTEREST,
            PlaceType::ESTABLISHMENT,
        ], $createPlace->types);
    }
}
