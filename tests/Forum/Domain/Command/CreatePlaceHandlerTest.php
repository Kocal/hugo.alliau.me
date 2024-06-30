<?php

declare(strict_types=1);

namespace App\Tests\Forum\Domain\Command;

use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Command\CreateAddressHandler;
use App\Places\Domain\Command\CreatePlace;
use App\Places\Domain\Command\CreatePlaceHandler;
use App\Places\Domain\PlaceType;
use App\Shared\Domain\Command\CommandBus;
use App\Tests\Forum\Infrastructure\Double\Repository\FakePlaceRepository;
use PHPUnit\Framework\TestCase;

final class CreatePlaceHandlerTest extends TestCase
{
    public function testCreatePlace(): void
    {
        $command = new CreatePlace(
            createAddress: new CreateAddress(
                name: 'name',
                coordinates: [41, 3],
                formattedAddress: 'formattedAddress',
                country: 'country',
                city: 'city',
            ),
            googleMapsUrl: 'googleMapsUrl',
            iconMaskUri: 'iconMaskUri',
            types: [PlaceType::AIRPORT],
        );

        $placeRepository = new FakePlaceRepository();

        $commandBus = new class() implements CommandBus {
            public function dispatch(object $command): mixed
            {
                if (! ($command instanceof CreateAddress)) {
                    throw new \InvalidArgumentException(sprintf('Unexpected command "%s".', get_debug_type($command)));
                }

                return (new CreateAddressHandler())($command);
            }
        };

        $handler = new CreatePlaceHandler($placeRepository, $commandBus);
        $place = $handler($command);

        self::assertSame('name', $place->getAddress()->getName());
        self::assertSame([41, 3], $place->getAddress()->getCoordinates());
        self::assertSame('formattedAddress', $place->getAddress()->getFormattedAddress());
        self::assertSame('country', $place->getAddress()->getCountry());
        self::assertSame('city', $place->getAddress()->getCity());
        self::assertSame('googleMapsUrl', $place->getGoogleMapsUrl());
        self::assertSame('iconMaskUri', $place->getIconMaskUri());
        self::assertSame([PlaceType::AIRPORT], $place->getTypes());
    }
}
