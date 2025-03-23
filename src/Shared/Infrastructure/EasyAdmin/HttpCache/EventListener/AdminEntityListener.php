<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EasyAdmin\HttpCache\EventListener;

use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use App\Shared\Domain\HttpCache\Exception\UnableToClearHttpCacheException;
use App\Shared\Domain\HttpCache\HttpCache;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class AdminEntityListener
{
    public function __construct(
        private HttpCache $httpCache,
        private LoggerInterface $logger,
    ) {
    }

    #[AsEventListener]
    public function afterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $this->clearCache($event->getEntityInstance());
    }

    #[AsEventListener]
    public function afterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $this->clearCache($event->getEntityInstance());
    }

    #[AsEventListener]
    public function afterEntityDeleted(AfterEntityDeletedEvent $event): void
    {
        $this->clearCache($event->getEntityInstance());
    }

    private function clearCache(object $entity): void
    {
        if ($entity instanceof CacheableEntity) {
            try {
                $this->httpCache->clearFor(CacheItem::fromEntity($entity));
                $this->logger->info('HTTP cache cleared for entity.', [
                    'entity' => $entity::class,
                ]);
            } catch (UnableToClearHttpCacheException $e) {
                $this->logger->error('Unable to clear HTTP cache.', [
                    'exception' => $e,
                ]);
            }
        }
    }
}
