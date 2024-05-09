<?php

namespace App\Shared\Google\Places\Client;

interface GooglePlacesClient
{
    public function textSearch(string $textQuery, array $fieldMasks = ['places.id', 'places.name']): array;
}