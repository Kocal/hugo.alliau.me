<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Command;

use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Command\CreateAddressHandler;
use App\Places\Domain\Command\CreatePlace;
use App\Places\Domain\Command\CreatePlaceHandler;
use App\Places\Domain\Data\Address;
use App\Places\Domain\Data\Place;
use App\Places\Domain\Data\PlaceType;
use App\Shared\Domain\CQRS\CommandBus;
use App\Tests\Places\Infrastructure\Double\Repository\FakePlaceRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreatePlaceHandler::class)]
#[CoversClass(CreateAddressHandler::class)]
#[CoversClass(CreatePlace::class)]
#[CoversClass(CreateAddress::class)]
#[CoversClass(Address::class)]
#[CoversClass(Place::class)]
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

        $this->assertSame('name', $place->getAddress()->getName());
        $this->assertSame([41, 3], $place->getAddress()->getCoordinates());
        $this->assertSame('formattedAddress', $place->getAddress()->getFormattedAddress());
        $this->assertSame('country', $place->getAddress()->getCountry());
        $this->assertSame('city', $place->getAddress()->getCity());
        $this->assertSame('googleMapsUrl', $place->getGoogleMapsUrl());
        $this->assertSame('iconMaskUri', $place->getIconMaskUri());
        $this->assertSame([PlaceType::AIRPORT], $place->getTypes());

        $this->assertSame([$place], $placeRepository->findAll());
    }
}
