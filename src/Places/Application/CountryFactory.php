<?php

declare(strict_types=1);

namespace App\Places\Application;

use App\Places\Domain\Data\Place;

final readonly class CountryFactory
{
    /**
     * @param iterable<Place> $places
     *
     * @return array<string, Country> Sorted by country name (locale fr_FR)
     */
    public function fromPlaces(iterable $places): array
    {
        /** @var array<string, list<array{0: float, 1: float}>> $coordinatesByCountry */
        $coordinatesByCountry = [];

        foreach ($places as $place) {
            $address = $place->getAddress();
            if ($address === null) {
                continue;
            }

            $country = $address->getCountry();
            if ($country === null) {
                continue;
            }

            $coordinatesByCountry[$country][] = $address->getCoordinates();
        }

        $countries = [];
        foreach ($coordinatesByCountry as $name => $coordinates) {
            $countries[$name] = new Country(
                placesCount: count($coordinates),
                coordinates: $coordinates,
            );
        }

        $collator = new \Collator('fr_FR');
        uksort($countries, static fn (string $a, string $b): int => (int) $collator->compare($a, $b));

        return $countries;
    }
}
