<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Foundry\Factory;

use App\User\Domain\Data\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array
    {
        return [
            'password' => self::faker()->text(),
            'roles' => [],
            'username' => self::faker()->text(30),
        ];
    }
}
