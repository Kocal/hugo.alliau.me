<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Foundry\Factory;

use App\User\Domain\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
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
