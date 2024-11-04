<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Doctrine\DBAL\Types;

use App\Shared\Domain\Data\ValueObject\ProjectId;
use Symfony\Bridge\Doctrine\Types\AbstractUidType;

final class ProjectIdType extends AbstractIdType
{
    public const string NAME = 'project_id';

    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getIdClass(): string
    {
        return ProjectId::class;
    }
}
