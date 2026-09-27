<?php

declare(strict_types=1);

namespace App\Places\Application\Command;

use App\Places\Domain\Data\Address;
use App\Places\Domain\Data\Place;
use App\Places\Domain\Repository\PlaceRepository;
use App\Shared\Application\CQRS\AsCommandHandler;

#[AsCommandHandler]
final readonly class CreatePlaceHandler
{
    public function __construct(
        private PlaceRepository $placeRepository,
    ) {
    }

    public function __invoke(CreatePlace $command): Place
    {
        $place = new Place()
            ->setAddress(Address::create(
                name: $command->name,
                coordinates: $command->coordinates,
                formattedAddress: $command->formattedAddress,
                country: $command->country,
                city: $command->city,
            ))
            ->setGoogleMapsUrl($command->googleMapsUrl)
            ->setIconMaskUri($command->iconMaskUri)
            ->setTypes($command->types)
        ;

        $this->placeRepository->add($place);

        return $place;
    }
}
