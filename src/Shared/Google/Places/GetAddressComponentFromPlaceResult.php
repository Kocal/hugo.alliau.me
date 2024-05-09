<?php

namespace App\Shared\Google\Places;

final class GetAddressComponentFromPlaceResult
{
    public function __invoke(array $placeResult, string $addressComponentType): array|null
    {
        foreach ($placeResult['addressComponents'] as $addressComponent) {
            if (in_array($addressComponentType, $addressComponent['types'], true)) {
                return $addressComponent;
            }
        }

        return null;
    }
}