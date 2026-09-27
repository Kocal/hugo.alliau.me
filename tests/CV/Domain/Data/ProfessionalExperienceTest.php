<?php

declare(strict_types=1);

namespace App\Tests\CV\Domain\Data;

use App\CV\Domain\Data\ProfessionalExperience;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

#[CoversClass(ProfessionalExperience::class)]
final class ProfessionalExperienceTest extends TestCase
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
        $experience = new ProfessionalExperience();
        $this->clock->modify('+1 day');

        $experience->preUpdate();

        $this->assertEquals($this->clock->now(), $experience->getUpdatedAt());
    }
}
