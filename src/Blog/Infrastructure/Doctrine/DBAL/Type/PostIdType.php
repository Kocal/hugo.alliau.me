<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Doctrine\DBAL\Type;

use App\Blog\Domain\Data\PostId;
use App\Shared\Infrastructure\Database\Doctrine\DBAL\Types\AbstractIdType;

final class PostIdType extends AbstractIdType
{
    public const string NAME = 'post_id';

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getIdClass(): string
    {
        return PostId::class;
    }
}
