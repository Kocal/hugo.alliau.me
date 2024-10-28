<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Domain\CQRS\CommandBus;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Leverages a message bus to expect a single, synchronous message handling and return its result.
 */
final class SymfonyMessengerCommandBus implements CommandBus
{
    use HandleTrait;

    public function __construct(
        #[Autowire(service: 'messenger.bus.command')]
        private MessageBusInterface $messageBus,
    ) {
    }

    #[\Override]
    public function dispatch(object $command): mixed
    {
        return $this->handle($command);
    }
}
