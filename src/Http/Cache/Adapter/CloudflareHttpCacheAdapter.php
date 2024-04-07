<?php

namespace App\Http\Cache\Adapter;

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
