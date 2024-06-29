<?php

declare(strict_types=1);

namespace App\Places\Domain\Command;

use App\Places\Domain\Address;
use App\Shared\Domain\Command\AsCommandHandler;

#[AsCommandHandler]
final readonly class CreateAddressHandler
{
    public function __invoke(CreateAddress $command): Address
    {
        $address = (new Address())
            ->setName($command->name)
            ->setCoordinates($command->coordinates)
            ->setFormattedAddress($command->formattedAddress)
            ->setCountry($command->country)
            ->setCity($command->city)
        ;

        return $address;
    }
}
