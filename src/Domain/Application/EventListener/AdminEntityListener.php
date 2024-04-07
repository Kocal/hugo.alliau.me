<?php

namespace App\Domain\Application\EventListener;

use App\Http\Cache\CacheableEntity;
use App\Http\Cache\HttpCache;
use App\Http\Cache\ValueObject\CacheItem;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AdminEntityListener
{
    public function __construct(
        private HttpCache $httpCache,
    ) {
    }

    #[AsEventListener]
    public function afterEntityPersist(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }

    #[AsEventListener]
    public function afterEntityUpdate(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }

    #[AsEventListener]
    public function afterEntityRemove(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }
}
