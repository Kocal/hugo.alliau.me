<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Foundry\Factory;

use App\Places\Domain\Place;
use App\Places\Domain\PlaceType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Place>
 */
final class PlaceFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Place::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'googleMapsUrl' => self::faker()->text(),
            'iconMaskUri' => self::faker()->text(),
            'types' => self::faker()->randomElement(PlaceType::cases()),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
