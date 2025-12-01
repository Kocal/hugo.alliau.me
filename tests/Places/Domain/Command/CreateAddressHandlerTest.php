<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Command;

use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Command\CreateAddressHandler;
use App\Places\Domain\Data\Address;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateAddressHandler::class)]
#[CoversClass(CreateAddress::class)]
#[UsesClass(Address::class)]
final class CreateAddressHandlerTest extends TestCase
{
    public function testCreateAddress(): void
    {
        $command = new CreateAddress(
            name: 'name',
            coordinates: [41, 3],
            formattedAddress: 'formattedAddress',
            country: 'country',
            city: 'city',
        );

        $handler = new CreateAddressHandler();
        $address = $handler($command);

        $this->assertSame('name', $address->getName());
        $this->assertSame([41, 3], $address->getCoordinates());
        $this->assertSame('formattedAddress', $address->getFormattedAddress());
        $this->assertSame('country', $address->getCountry());
        $this->assertSame('city', $address->getCity());
    }
}
