<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain;

use App\Places\Domain\PlaceType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\Translation\t;

#[CoversClass(PlaceType::class)]
class PlaceTypeTest extends TestCase
{
    public function testToTranslatable(): void
    {
        $this->assertEquals(t('place_type.hostel'), PlaceType::HOSTEL->toTranslatable());
    }
}
