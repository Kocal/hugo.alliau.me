<?php

namespace App\Shared\Google\Places;

final class GetAddressComponent
{
    /**
     * @param array<string, mixed> $addressComponents
     * @return array<string, mixed>|null
     */
    public function __invoke(array $addressComponents, string $addressComponentType): array|null
    {
        foreach ($addressComponents as $addressComponent) {
            if (in_array($addressComponentType, $addressComponent['types'], true)) {
                return $addressComponent;
            }
        }

        return null;
    }
}
