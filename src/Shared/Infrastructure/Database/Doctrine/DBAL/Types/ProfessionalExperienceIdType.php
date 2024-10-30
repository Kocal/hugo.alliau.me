<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Doctrine\DBAL\Types;

use App\Shared\Domain\Data\ValueObject\ProfessionalExperienceId;
use Symfony\Bridge\Doctrine\Types\AbstractUidType;

final class ProfessionalExperienceIdType extends AbstractUidType
{
    public const string NAME = 'professional_experience_id';

    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    protected function getUidClass(): string
    {
        return ProfessionalExperienceId::class;
    }
}
