<?php

declare(strict_types=1);

namespace App\Places\Domain\Data;

use App\Shared\Domain\Data\UuidTrait;
use App\Shared\Domain\Data\ValueObject\Id;

final class PlaceId implements Id
{
    use UuidTrait;
}
