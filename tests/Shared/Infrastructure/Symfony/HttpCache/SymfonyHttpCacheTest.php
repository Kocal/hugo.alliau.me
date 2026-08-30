<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Symfony\HttpCache;

use App\Shared\Domain\HttpCache\Adapter\HttpCacheAdapter;
use App\Shared\Domain\HttpCache\CacheItem;
use App\Shared\Infrastructure\Symfony\HttpCache\SymfonyHttpCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(SymfonyHttpCache::class)]
#[UsesClass(CacheItem::class)]
final class SymfonyHttpCacheTest extends KernelTestCase
{
    public function testItPurgesEveryLocaleVariantOfARoute(): void
    {
        self::bootKernel();
        $urlGenerator = self::getContainer()->get('router');
        $this->assertInstanceOf(UrlGeneratorInterface::class, $urlGenerator);

        $adapter = new class() implements HttpCacheAdapter {
            /**
             * @var list<string>
             */
            public array $cleared = [];

            #[\Override]
            public function clearAll(): void
            {
            }

            #[\Override]
            public function clearUrls(string ...$urls): void
            {
                $this->cleared = array_values($urls);
            }
        };

        $cache = new SymfonyHttpCache($adapter, $urlGenerator, new NullLogger(), ['en', 'fr']);
        $cache->clearFor(CacheItem::fromRoute('app.cv.index'));

        $paths = array_map(static fn (string $url): string => (string) parse_url($url, PHP_URL_PATH), $adapter->cleared);
        $this->assertContains('/cv', $paths);
        $this->assertContains('/fr/cv', $paths);
    }
}
