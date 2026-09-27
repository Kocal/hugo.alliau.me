<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Domain\Data;

use App\Recipes\Domain\Data\Ingredient;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Step;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecipeContent::class)]
#[UsesClass(Step::class)]
#[UsesClass(Ingredient::class)]
final class RecipeContentTest extends TestCase
{
    public function testItAcceptsAnEmptyTree(): void
    {
        $content = new RecipeContent();

        $this->assertSame([], $content->roots);
    }

    public function testItAcceptsAValidTree(): void
    {
        $content = new RecipeContent([
            new Step('s1', 'Mélanger', [
                new Ingredient('i1', 'de farine'),
                new Ingredient('i2', "d'eau"),
            ]),
        ]);

        $this->assertCount(1, $content->roots);
    }

    public function testItListsStepsAfterTheStepsTheyConsume(): void
    {
        $sift = new Step('s1', 'Tamiser', [new Ingredient('i1', 'de farine')]);
        $whisk = new Step('s2', 'Fouetter', [new Ingredient('i2', "d'œufs")]);
        $mix = new Step('s3', 'Mélanger', [$sift, $whisk]);
        $sauce = new Step('s4', 'Faire la sauce', [new Ingredient('i3', 'de crème')]);

        $content = new RecipeContent([$mix, new Ingredient('i4', 'de sel'), $sauce]);

        $this->assertSame([$sift, $whisk, $mix, $sauce], $content->stepsInExecutionOrder());
    }

    public function testAnEmptyTreeHasNoSteps(): void
    {
        $this->assertSame([], new RecipeContent()->stepsInExecutionOrder());
        $this->assertSame([], new RecipeContent([new Ingredient('i1', 'de sel')])->stepsInExecutionOrder());
    }

    public function testItRejectsTwoRootsSharingAnId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate recipe node id "dup".');

        new RecipeContent([
            new Ingredient('dup', 'de sel'),
            new Ingredient('dup', 'de poivre'),
        ]);
    }

    public function testItRejectsAStepAndANestedIngredientSharingAnId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate recipe node id "x1".');

        new RecipeContent([
            new Step('x1', 'Mélanger', [
                new Ingredient('i1', 'de sel'),
            ]),
            new Ingredient('x1', 'de poivre'),
        ]);
    }

    public function testItRejectsAnEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A recipe node must have a non-empty id.');

        new RecipeContent([new Ingredient('', 'de sel')]);
    }

    public function testItRejectsAnIngredientWithABlankLabel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Ingredient "i1" must have a non-empty label.');

        new RecipeContent([new Ingredient('i1', '   ')]);
    }

    public function testItRejectsAStepWithABlankText(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Step "s1" must have a non-empty text.');

        new RecipeContent([new Step('s1', '  ', [new Ingredient('i1', 'de sel')])]);
    }
}
