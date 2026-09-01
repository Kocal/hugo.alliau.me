<?php

declare(strict_types=1);

namespace App\Tests\Recipes\Domain\Data;

use App\Recipes\Domain\Data\Recipe;
use App\Recipes\Domain\Data\RecipeContent;
use App\Recipes\Domain\Data\Route;
use App\Shared\Domain\HttpCache\CacheItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Recipe::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(CacheItem::class)]
final class RecipeTest extends TestCase
{
    public function testItStartsEmptyAndHidden(): void
    {
        $recipe = new Recipe();

        $this->assertFalse($recipe->isVisible());
        $this->assertSame(4, $recipe->getServings());
        $this->assertSame([], $recipe->getContent()->roots);
    }

    public function testEtagIsScopedToTheContext(): void
    {
        $recipe = new Recipe();

        $this->assertStringStartsWith('recipes:recipe:' . $recipe->getId(), $recipe->getEtag());
    }

    public function testCacheItemsCoverTheListAndItsOwnPage(): void
    {
        $recipe = new Recipe()
            ->setSlug('nouilles-udon');

        $this->assertEquals([
            CacheItem::fromRoute(Route::LIST),
            CacheItem::fromRoute(Route::VIEW, [
                'slug' => 'nouilles-udon',
            ]),
        ], $recipe->getCacheItems());
    }
}
