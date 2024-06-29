<?php
declare(strict_types=1);

namespace App\Shared\Domain\Command;

interface CommandBus
{
    public function dispatch(object $command): mixed;
}
