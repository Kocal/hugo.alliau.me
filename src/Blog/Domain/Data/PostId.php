<?php

declare(strict_types=1);

namespace App\Blog\Domain\Data;

use App\Shared\Domain\Data\UuidTrait;
use App\Shared\Domain\Data\ValueObject\Id;

final class PostId implements Id
{
    use UuidTrait;
}
