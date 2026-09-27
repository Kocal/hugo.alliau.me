<?php

declare(strict_types=1);

namespace App\Tests\Places\Application\Command;

use App\Places\Application\Command\CreatePlace;
use App\Places\Domain\Data\PlaceType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreatePlace::class)]
final class CreatePlaceTest extends TestCase
{
    public function testConstruct(): void
    {
        $createPlace = new CreatePlace(
            name: 'name',
            coordinates: [41, 3],
            formattedAddress: 'formattedAddress',
            country: 'country',
            city: 'city',
            googleMapsUrl: 'googleMapsUrl',
            iconMaskUri: 'iconMaskUri',
            types: [PlaceType::AIRPORT],
        );

        $this->assertSame('name', $createPlace->name);
        $this->assertSame([41, 3], $createPlace->coordinates);
        $this->assertSame('formattedAddress', $createPlace->formattedAddress);
        $this->assertSame('country', $createPlace->country);
        $this->assertSame('city', $createPlace->city);
        $this->assertSame('googleMapsUrl', $createPlace->googleMapsUrl);
        $this->assertSame('iconMaskUri', $createPlace->iconMaskUri);
        $this->assertSame([PlaceType::AIRPORT], $createPlace->types);
    }
}
