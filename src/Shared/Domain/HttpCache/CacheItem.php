<?php

declare(strict_types=1);

namespace App\Shared\Domain\HttpCache;

final readonly class CacheItem
{
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

    /**
     * @param array<string,mixed> $parameters
     */
    public static function fromRoute(string|\BackedEnum $route, array $parameters = []): self
    {
        if ($route instanceof \BackedEnum) {
            $route = (string) $route->value;
        }

        return new self(route: $route, parameters: $parameters);
    }

    public static function fromEntity(CacheableEntity $entity): self
    {
        return new self(entity: $entity);
    }
}
