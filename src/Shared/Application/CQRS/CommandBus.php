<?php

declare(strict_types=1);

namespace App\Shared\Application\CQRS;

interface CommandBus
{
    public function dispatch(object $command): mixed;
}
