<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Doctrine\DBAL\Types;

use App\Shared\Domain\Data\ValueObject\PlaceId;
use Symfony\Bridge\Doctrine\Types\AbstractUidType;

final class PlaceIdType extends AbstractUidType
{
    public const string NAME = 'place_id';

    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getUidClass(): string
    {
        return PlaceId::class;
    }
}
