<?php

declare(strict_types=1);

namespace App\User\Domain\Data;

use App\Shared\Domain\Data\UuidTrait;
use App\Shared\Domain\Data\ValueObject\Id;

final class UserId implements Id
{
    use UuidTrait;
}
