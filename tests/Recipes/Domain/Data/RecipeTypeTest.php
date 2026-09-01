<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Domain\Data;

use App\Recipes\Domain\Data\RecipeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\Translation\t;

#[CoversClass(RecipeType::class)]
final class RecipeTypeTest extends TestCase
{
    public function testToTranslatable(): void
    {
        $this->assertEquals(t('recipe_type.starter'), RecipeType::STARTER->toTranslatable());
        $this->assertEquals(t('recipe_type.main'), RecipeType::MAIN->toTranslatable());
        $this->assertEquals(t('recipe_type.dessert'), RecipeType::DESSERT->toTranslatable());
    }
}
