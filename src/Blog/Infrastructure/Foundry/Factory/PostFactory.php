<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Foundry\Factory;

use App\Blog\Domain\Post;
use App\Blog\Domain\PostStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Post>
 */
final class PostFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Post::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'content' => self::faker()->text(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'description' => self::faker()->text(255),
            'seo' => PostSeoFactory::new(),
            'slug' => self::faker()->text(255),
            'status' => self::faker()->randomElement(PostStatus::cases()),
            'tags' => [],
            'title' => self::faker()->text(255),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
