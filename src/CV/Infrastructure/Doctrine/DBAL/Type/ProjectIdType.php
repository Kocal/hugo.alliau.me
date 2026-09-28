<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Doctrine\DBAL\Type;

use App\CV\Domain\Data\ProjectId;
use App\Shared\Infrastructure\Database\Doctrine\DBAL\Types\AbstractIdType;

final class ProjectIdType extends AbstractIdType
{
    public const string NAME = 'project_id';

    #[\Override]
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
