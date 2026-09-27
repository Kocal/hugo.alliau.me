<?php

declare(strict_types=1);

namespace App\Places\Infrastructure\Doctrine\DBAL\Type;

use App\Places\Domain\Data\PlaceId;
use App\Shared\Infrastructure\Database\Doctrine\DBAL\Types\AbstractIdType;

final class PlaceIdType extends AbstractIdType
{
    public const string NAME = 'place_id';

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getIdClass(): string
    {
        return PlaceId::class;
    }
}
