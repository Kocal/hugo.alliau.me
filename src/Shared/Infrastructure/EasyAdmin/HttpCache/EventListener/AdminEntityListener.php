<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EasyAdmin\HttpCache\EventListener;

use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use App\Shared\Domain\HttpCache\HttpCache;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class AdminEntityListener
{
    public function __construct(
        private HttpCache $httpCache,
    ) {
    }

    #[AsEventListener]
    public function afterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }

    #[AsEventListener]
    public function afterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }

    #[AsEventListener]
    public function afterEntityDeleted(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }
}
