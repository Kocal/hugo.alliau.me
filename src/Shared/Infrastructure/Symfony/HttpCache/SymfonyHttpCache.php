<?php

namespace App\Shared\Infrastructure\Symfony\HttpCache;

use App\Shared\Domain\HttpCache\CacheItem;
use App\Shared\Domain\HttpCache\HttpCache;
use App\Shared\Domain\HttpCache\HttpCacheAdapter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SymfonyHttpCache implements HttpCache
{
    public function __construct(
        private HttpCacheAdapter $httpCacheAdapter,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }

    public function clearAll(): void
    {
        $this->logger->info('Clearing all HTTP cache.', [
            'adapter' => $this->httpCacheAdapter::class,
        ]);

        $this->httpCacheAdapter->clearAll();
    }

    public function clearFor(CacheItem ...$cacheItems): void
    {
        $normalized = $this->normalize(...$cacheItems);

        $this->logger->info('Clearing HTTP cache.', [
            'adapter' => $this->httpCacheAdapter::class,
            'urls' => $normalized['urls'],
        ]);

        $this->httpCacheAdapter->clearUrls(...$normalized['urls']);
    }

    /**
     * @return array{urls: list<string>}
     */
    private function normalize(CacheItem ...$cacheItem): array
    {
        $normalized = [
            'urls' => [],
        ];

        foreach ($cacheItem as $item) {
            if ($item->route !== null) {
                $normalized['urls'][] = $this->urlGenerator->generate($item->route, $item->parameters, UrlGeneratorInterface::ABSOLUTE_URL);
            } elseif ($item->entity !== null) {
                $normalized = array_merge_recursive($normalized, $this->normalize(...$item->entity->getCacheItems()));
            }
        }

        $normalized['urls'] = array_unique($normalized['urls']);

        return $normalized;
    }
}
