<?php

namespace App\Shared\Http\Cache\Adapter;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CloudflareHttpCacheAdapter implements HttpCacheAdapter
{
    public function __construct(
        #[Target('cloudflare.client')]
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'CLOUDFLARE_ZONE_ID')]
        private string $zoneId,
        private LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function clearAll(): void
    {
        $this->logger->info('Purging all HTTP cache.', [
            'adapter' => self::class,
        ]);

        $response = $this->httpClient->request('POST', 'zones/{zone_id}/purge_cache', [
            'vars' => [
                'zone_id' => $this->zoneId,
            ],
            'json' => [
                'purge_everything' => true,
            ],
        ]);

        $this->logger->info('Purged all HTTP cache.', [
            'adapter' => self::class,
            'status' => $response->getStatusCode(),
            'response' => $response->toArray(false),
        ]);
    }

    #[\Override]
    public function clearUrls(string ...$urls): void
    {
        foreach (array_chunk($urls, 30) as $chunk) {
            $this->logger->info('Purging HTTP cache.', [
                'adapter' => self::class,
                'urls' => $chunk,
            ]);

            $response = $this->httpClient->request('POST', 'zones/{zone_id}/purge_cache', [
                'vars' => [
                    'zone_id' => $this->zoneId,
                ],
                'json' => [
                    'files' => $chunk,
                ],
            ]);

            $this->logger->info('Purged HTTP cache.', [
                'adapter' => self::class,
                'urls' => $chunk,
                'status' => $response->getStatusCode(),
                'response' => $response->toArray(false),
            ]);
        }
    }
}
