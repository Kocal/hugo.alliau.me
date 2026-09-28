<?php

declare(strict_types=1);

namespace App\CV\Domain\Data;

use App\Shared\Domain\Data\UuidTrait;
use App\Shared\Domain\Data\ValueObject\Id;

final class ProfessionalExperienceId implements Id
{
    use UuidTrait;
}
