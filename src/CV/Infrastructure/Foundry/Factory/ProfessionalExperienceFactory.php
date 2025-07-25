<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Foundry\Factory;

use App\CV\Domain\ProfessionalExperience;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProfessionalExperience>
 */
final class ProfessionalExperienceFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return ProfessionalExperience::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'badges' => [],
            'company' => self::faker()->text(255),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'description' => self::faker()->text(),
            'jobName' => self::faker()->text(255),
            'startDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'url' => self::faker()->text(255),
        ];
    }
}
