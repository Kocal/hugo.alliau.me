<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Domain\Data;

use App\Recipes\Domain\Data\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\Translation\t;

#[CoversClass(Unit::class)]
final class UnitTest extends TestCase
{
    public function testToTranslatableCarriesTheCount(): void
    {
        $this->assertEquals(t('unit.tbsp', [
            'count' => 2.0,
        ]), Unit::TBSP->toTranslatable(2.0));
    }

    public function testEveryCaseIsBackedByItsLowercasedName(): void
    {
        foreach (Unit::cases() as $unit) {
            $this->assertSame(strtolower($unit->name), $unit->value);
        }
    }
}
