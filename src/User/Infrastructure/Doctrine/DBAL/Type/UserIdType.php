<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\DBAL\Type;

use App\Shared\Infrastructure\Database\Doctrine\DBAL\Types\AbstractIdType;
use App\User\Domain\Data\UserId;

final class UserIdType extends AbstractIdType
{
    public const string NAME = 'user_id';

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getIdClass(): string
    {
        return UserId::class;
    }
}
