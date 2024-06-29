<?php

namespace App\Places\Domain\Command;

use App\Places\Domain\Place;
use App\Places\Domain\Repository\PlaceRepository;
use App\Shared\Domain\Command\AsCommandHandler;
use App\Shared\Domain\Command\CommandBus;

#[AsCommandHandler]
final readonly class CreatePlaceHandler
{
    public function __construct(
        private PlaceRepository $placeRepository,
        private CommandBus $commandBus,
    ) {
    }

    public function __invoke(CreatePlace $command): Place
    {
        $place = (new Place())
            ->setAddress($this->commandBus->dispatch($command->createAddress))
            ->setGoogleMapsUrl($command->googleMapsUrl)
            ->setIconMaskUri($command->iconMaskUri)
            ->setTypes($command->types)
        ;

        $this->placeRepository->add($place);

        return $place;
    }
}
