<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Domain\Data;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\Step;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Step::class)]
#[UsesClass(Ingredient::class)]
final class StepTest extends TestCase
{
    public function testItRejectsAStepWithoutChildren(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Step "s1" must have at least one child.');

        new Step('s1', 'Mélanger', []);
    }

    public function testItAcceptsIngredientsAndStepsAsChildren(): void
    {
        $flour = new Ingredient('i1', 'de farine', 200.0);
        $water = new Ingredient('i2', "d'eau", 100.0);
        $inner = new Step('s1', 'Tamiser', [$flour]);
        $outer = new Step('s2', 'Mélanger', [$inner, $water]);

        $this->assertSame([$inner, $water], $outer->children);
    }
}
