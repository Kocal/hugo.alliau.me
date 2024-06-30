<?php
declare(strict_types=1);

namespace App\Tests\Forum\Domain\Command;

use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Command\CreateAddressHandler;
use PHPUnit\Framework\TestCase;

final  class CreateAddressHandlerTest extends TestCase
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

        self::assertSame('name', $address->getName());
        self::assertSame([41, 3], $address->getCoordinates());
        self::assertSame('formattedAddress', $address->getFormattedAddress());
        self::assertSame('country', $address->getCountry());
        self::assertSame('city', $address->getCity());
    }
}
