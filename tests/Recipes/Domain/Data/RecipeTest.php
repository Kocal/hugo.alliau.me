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
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

#[CoversClass(Recipe::class)]
#[UsesClass(RecipeContent::class)]
#[UsesClass(CacheItem::class)]
final class RecipeTest extends TestCase
{
    private ClockInterface $previousClock;

    private MockClock $clock;

    protected function setUp(): void
    {
        $this->previousClock = Clock::get();
        $this->clock = new MockClock('2026-01-01 10:00:00');
        Clock::set($this->clock);
    }

    protected function tearDown(): void
    {
        Clock::set($this->previousClock);
    }

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

    public function testPreUpdateStampsUpdatedAtWithTheClock(): void
    {
        $recipe = new Recipe();
        $this->clock->modify('+1 day');

        $recipe->preUpdate();

        $this->assertEquals($this->clock->now(), $recipe->getUpdatedAt());
    }
}
