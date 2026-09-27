<?php

declare(strict_types=1);

namespace App\Tests\CV\Domain\Data;

use App\CV\Domain\Data\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

#[CoversClass(Project::class)]
final class ProjectTest extends TestCase
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
        $project = new Project();
        $this->clock->modify('+1 day');

        $project->preUpdate();

        $this->assertEquals($this->clock->now(), $project->getUpdatedAt());
    }

    public function testEtagIsScopedToProjects(): void
    {
        $project = new Project();

        $this->assertStringStartsWith('cv:project:' . $project->getId(), $project->getEtag());
    }
}
