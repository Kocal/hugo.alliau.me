<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Foundry\Factory;

use App\Blog\Domain\Data\Post;
use App\Blog\Domain\Data\PostStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
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
    protected function defaults(): array
    {
        return [
            'content' => self::faker()->text(),
            'description' => self::faker()->text(255),
            'seo' => PostSeoFactory::new(),
            'slug' => self::faker()->text(255),
            'status' => self::faker()->randomElement(PostStatus::cases()),
            'tags' => [],
            'title' => self::faker()->text(255),
        ];
    }
}
