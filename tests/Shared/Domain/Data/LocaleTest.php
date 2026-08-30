<?php

declare(strict_types=1);

namespace App\Tests\Shared\Domain\Data;

use App\Shared\Domain\Data\Locale;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Locale::class)]
final class LocaleTest extends TestCase
{
    public function testItHasEnglishAndFrenchCases(): void
    {
        $this->assertSame('en', Locale::EN->value);
        $this->assertSame('fr', Locale::FR->value);
    }

    public function testDefaultIsEnglish(): void
    {
        $this->assertSame(Locale::EN, Locale::default());
    }

    public function testFlagReturnsTheMatchingEmoji(): void
    {
        $this->assertSame('🇬🇧', Locale::EN->flag());
        $this->assertSame('🇫🇷', Locale::FR->flag());
    }

    public function testToTranslatableUsesTheLocaleKey(): void
    {
        $this->assertSame('locale.en', Locale::EN->toTranslatable()->getMessage());
        $this->assertSame('locale.fr', Locale::FR->toTranslatable()->getMessage());
    }
}
