<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Foundry\Factory;

use App\CV\Domain\Data\ProfessionalExperience;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ProfessionalExperience>
 */
final class ProfessionalExperienceFactory extends PersistentObjectFactory
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
    protected function defaults(): array
    {
        return [
            'company' => self::faker()->text(255),
            'startDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'url' => self::faker()->url(),
        ];
    }
}
