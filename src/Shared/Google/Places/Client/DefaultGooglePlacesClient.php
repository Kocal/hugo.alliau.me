<?php

namespace App\Shared\Google\Places\Client;

use App\Shared\Google\Places\Exception\NoPlacesException;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DefaultGooglePlacesClient implements GooglePlacesClient
{
    public function __construct(
        #[Target('google.places.client')] private HttpClientInterface $httpClient,
        #[Target('cache.google.places')] private CacheInterface $cache,
    )
    {
    }

    #[\Override] public function textSearch(string $textQuery, array $fieldMasks = ['places.id', 'places.name']): array
    {
        $data = $this->cache->get(self::getCacheKey($textQuery, $fieldMasks), function (CacheItemInterface $cacheItem) use ($textQuery, $fieldMasks) {
            $cacheItem->expiresAfter(60 * 60 * 24 * 30);

            $response = $this->httpClient->request('POST', '/v1/places:searchText', [
                'json' => [
                    'textQuery' => $textQuery,
                    'languageCode' => 'fr',
                ],
                'headers' => [
                    'X-Goog-FieldMask' => implode(',', $fieldMasks)
                ]
            ]);

            return $response->toArray();
        });

        if (!isset($data['places'])) {
            throw new NoPlacesException($textQuery);
        }

        return $data;
    }

    private static function getCacheKey(string $textQuery, array $fieldMasks): string
    {
        $sortedFieldMasks = [...$fieldMasks];
        sort($sortedFieldMasks);

        return hash('xxh3', $textQuery . implode(',', $sortedFieldMasks));
    }
}