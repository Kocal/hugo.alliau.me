<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Foundry\Factory;

use App\Blog\Domain\PostSeo;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<PostSeo>
 */
final class PostSeoFactory extends ObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return PostSeo::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array
    {
        return [];
    }
}
