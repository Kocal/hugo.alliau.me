<?php

declare(strict_types=1);

namespace App\Tests\Places\Domain\Data;

use App\Places\Domain\Data\Place;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

#[CoversClass(Place::class)]
final class PlaceTest extends TestCase
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

    public function testPreUpdateStampsUpdatedAtWithTheClock(): void
    {
        $place = new Place();
        $this->clock->modify('+1 day');

        $place->preUpdate();

        $this->assertEquals($this->clock->now(), $place->getUpdatedAt());
    }
}
