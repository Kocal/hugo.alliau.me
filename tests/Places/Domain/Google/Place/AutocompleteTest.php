<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Google\Place;

use App\Places\Domain\Data\Google\Place\AddressComponent;
use App\Places\Domain\Data\Google\Place\Autocomplete;
use App\Places\Domain\Data\Google\Place\Geometry;
use App\Places\Domain\Data\Google\Place\Location;
use App\Places\Domain\Data\PlaceType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Autocomplete::class)]
#[CoversClass(AddressComponent::class)]
#[CoversClass(Geometry::class)]
#[CoversClass(Location::class)]
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
}
