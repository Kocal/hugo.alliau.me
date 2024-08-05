<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Command;

use App\Places\Domain\Address;
use App\Places\Domain\Command\CreateAddress;
use App\Places\Domain\Command\CreateAddressHandler;
use App\Places\Domain\Command\CreatePlace;
use App\Places\Domain\Command\CreatePlaceHandler;
use App\Places\Domain\Place;
use App\Places\Domain\PlaceType;
use App\Shared\Domain\Command\CommandBus;
use App\Tests\Places\Infrastructure\Double\Repository\FakePlaceRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreatePlaceHandler::class)]
#[UsesClass(CreatePlace::class)]
#[UsesClass(CreateAddress::class)]
#[UsesClass(CreateAddressHandler::class)]
#[UsesClass(Address::class)]
#[UsesClass(Place::class)]
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
