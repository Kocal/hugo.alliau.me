<?php

namespace App\Http\Cache\Adapter;

use App\Http\Cache\HttpCache;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CloudflareHttpCacheAdapter implements HttpCache
{
    public function __construct(
        #[Target('cloudflare.client')]
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'CLOUDFLARE_ZONE_ID')]
        private string $zoneId,
    ) {
    }

    #[\Override] public function clearUrls(string ...$urls): void
    {
        $this->httpClient->request('POST', 'zones/{zone_id}/purge_cache', [
            'vars' => ['zone_id' => $this->zoneId],
            'json' => [
                'files' => $urls,
            ],
        ]);
    }
}