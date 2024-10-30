<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Doctrine\DBAL\Types;

use App\Shared\Domain\Data\ValueObject\UserId;
use Symfony\Bridge\Doctrine\Types\AbstractUidType;

final class UserIdType extends AbstractUidType
{
    public const string NAME = 'user_id';

    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getUidClass(): string
    {
        return UserId::class;
    }
}
