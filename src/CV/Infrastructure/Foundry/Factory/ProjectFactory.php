<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Foundry\Factory;

use App\CV\Domain\Project;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class ProjectFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Project::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'date' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'description' => self::faker()->text(),
            'name' => self::faker()->text(255),
            'techStack' => [],
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'url' => self::faker()->text(255),
        ];
    }
}
