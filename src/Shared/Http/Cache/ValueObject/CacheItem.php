<?php

namespace App\Shared\Http\Cache\ValueObject;

use App\Shared\Http\Cache\CacheableEntity;

final readonly class CacheItem
{
    /**
     * @param array<string,mixed> $parameters
     */
    public static function fromRoute(string $route, array $parameters = []): self
    {
        return new self(route: $route, parameters: $parameters);
    }

    public static function fromEntity(CacheableEntity $entity): self
    {
        return new self(entity: $entity);
    }

    private function __construct(
        /**
         * @internal
         */
        public string|null $route = null,
        /**
         * @internal
         * @var array<string, mixed>
         */
        public array $parameters = [],
        /**
         * @internal
         */
        public CacheableEntity|null $entity = null,
    ) {
    }
}
