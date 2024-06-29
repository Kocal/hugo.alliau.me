<?php

declare(strict_types=1);

namespace App\Shared\Domain\Mapper\Command;

use App\Shared\Domain\Command\AsCommandHandler;
use App\Shared\Domain\Mapper\ObjectMapper;

#[AsCommandHandler]
final readonly class MapObjectHandler
{
    public function __construct(
        private ObjectMapper $objectMapper,
    ) {
    }

    /**
     * @template Class of object
     * @param MapObject<Class> $command
     * @return Class
     */
    public function __invoke(MapObject $command): object
    {
        return $this->objectMapper->map($command->className, $command->source, $command->format);
    }
}
