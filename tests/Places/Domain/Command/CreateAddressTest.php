<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Command;

use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Google\Place\AddressComponent;
use App\Places\Domain\Google\Place\Autocomplete;
use App\Places\Domain\Google\Place\Geometry;
use App\Places\Domain\Google\Place\Location;
use App\Tests\Places\Domain\Factory\AutocompleteFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateAddress::class)]
#[CoversClass(Autocomplete::class)]
#[UsesClass(AddressComponent::class)]
#[UsesClass(Geometry::class)]
#[UsesClass(Location::class)]
class CreateAddressTest extends TestCase
{
    public function testConstruct(): void
    {
        $createAddress = new CreateAddress(
            name: 'name',
            coordinates: [41, 3],
            formattedAddress: 'formattedAddress',
            country: 'country',
            city: 'city',
        );

        $this->assertSame('name', $createAddress->name);
        $this->assertSame([41, 3], $createAddress->coordinates);
        $this->assertSame('formattedAddress', $createAddress->formattedAddress);
        $this->assertSame('country', $createAddress->country);
        $this->assertSame('city', $createAddress->city);
    }

    public function testFromGoogleAutocomplete(): void
    {
        $createAddress = CreateAddress::fromGoogleAutocomplete(AutocompleteFactory::seoulTower());

        $this->assertSame('N Seoul Tower', $createAddress->name);
        $this->assertSame([37.5511694, 126.9882266], $createAddress->coordinates);
        $this->assertSame('105 Namsangongwon-gil, Yongsan District, Seoul, Corée du Sud', $createAddress->formattedAddress);
        $this->assertSame('Corée du Sud', $createAddress->country);
        $this->assertSame('Seoul', $createAddress->city);
    }
}
