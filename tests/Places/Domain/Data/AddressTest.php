<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Data;

use App\Places\Domain\Data\Address;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Address::class)]
final class AddressTest extends TestCase
{
    public function testCreate(): void
    {
        $address = Address::create(
            name: 'name',
            coordinates: [41, 3],
            formattedAddress: 'formattedAddress',
            country: 'country',
            city: 'city',
        );

        $this->assertSame('name', $address->getName());
        $this->assertSame([41, 3], $address->getCoordinates());
        $this->assertSame('formattedAddress', $address->getFormattedAddress());
        $this->assertSame('country', $address->getCountry());
        $this->assertSame('city', $address->getCity());
    }
}
