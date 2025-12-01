<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Foundry\Factory;

use App\Places\Domain\Data\Address;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<Address>
 */
final class AddressFactory extends ObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Address::class;
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
