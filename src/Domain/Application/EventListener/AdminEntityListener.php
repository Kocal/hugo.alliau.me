<?php

namespace App\Domain\Application\EventListener;

use App\Http\Cache\CacheableEntity;
use App\Http\Cache\HttpCache;
use App\Http\Cache\ValueObject\CacheItem;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AdminEntityListener
{
    public function __construct(
        private HttpCache $httpCache,
        private LoggerInterface $logger,
    ) {
    }

    #[AsEventListener]
    public function afterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->logger->info('Got event {event_class}.', [
            'event_class' => $event::class,
            'entity_class' => $entity::class,
        ]);

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }

    #[AsEventListener]
    public function afterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->logger->info('Got event {event_class}.', [
            'event_class' => $event::class,
            'entity_class' => $entity::class,
        ]);

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }

    #[AsEventListener]
    public function afterEntityDeleted(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->logger->info('Got event {event_class}.', [
            'event_class' => $event::class,
            'entity_class' => $entity::class,
        ]);

        if ($entity instanceof CacheableEntity) {
            $this->httpCache->clearFor(CacheItem::fromEntity($entity));
        }
    }
}
