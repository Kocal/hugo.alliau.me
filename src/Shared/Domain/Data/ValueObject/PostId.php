<?php

declare(strict_types=1);

namespace App\Shared\Domain\Data\ValueObject;

use App\Shared\Domain\Data\UuidTrait;

final class PostId implements Id
{
    use UuidTrait;
}
