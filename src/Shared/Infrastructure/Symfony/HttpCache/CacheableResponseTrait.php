<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\HttpCache;

use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-require-extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 */
trait CacheableResponseTrait
{
    use CacheMethodsTrait;

    /**
     * @param list<string|CacheableEntity|null> $etag
     */
    private function createCacheableResponse(array $etag, ?\DateTimeInterface $lastModified, int $sharedMaxAge): Response
    {
        $buildTime = $this->getParameter('container.build_time');
        \assert(\is_int($buildTime));

        // Each deploy rebuilds the container, so validators from a previous deploy no longer match and pages reload with the new assets
        $builtAt = new \DateTimeImmutable('@' . $buildTime);

        $response = new Response();
        $response->setEtag(self::computeEtag((string) $buildTime, ...$etag));
        $response->setLastModified(max($lastModified, $builtAt));
        $response->setMaxAge(0);
        $response->setSharedMaxAge($sharedMaxAge);

        return $response;
    }
}
