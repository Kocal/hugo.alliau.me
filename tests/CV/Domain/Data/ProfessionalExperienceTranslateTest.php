<?php

declare(strict_types=1);

namespace App\Tests\CV\Domain\Data;

use App\CV\Domain\Data\ProfessionalExperience;
use App\CV\Domain\Data\ProfessionalExperienceTranslation;
use App\Shared\Domain\Data\Locale;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProfessionalExperience::class)]
#[UsesClass(ProfessionalExperienceTranslation::class)]
#[UsesClass(Locale::class)]
final class ProfessionalExperienceTranslateTest extends TestCase
{
    private function translation(Locale $locale, string $jobName): ProfessionalExperienceTranslation
    {
        return new ProfessionalExperienceTranslation()
            ->setLocale($locale)
            ->setJobName($jobName)
            ->setDescription('description');
    }

    public function testItReturnsTheRequestedLocale(): void
    {
        $experience = new ProfessionalExperience();
        $experience->addTranslation($this->translation(Locale::EN, 'Developer'));
        $experience->addTranslation($this->translation(Locale::FR, 'Développeur'));

        $this->assertSame('Developer', $experience->translate('en')?->getJobName());
        $this->assertSame('Développeur', $experience->translate('fr')?->getJobName());
    }

    public function testItFallsBackToEnglishForAnUnknownLocale(): void
    {
        $experience = new ProfessionalExperience();
        $experience->addTranslation($this->translation(Locale::EN, 'Developer'));
        $experience->addTranslation($this->translation(Locale::FR, 'Développeur'));

        $this->assertSame('Developer', $experience->translate('de')?->getJobName());
    }

    public function testItFallsBackToFrenchWhenEnglishIsMissing(): void
    {
        $experience = new ProfessionalExperience();
        $experience->addTranslation($this->translation(Locale::FR, 'Développeur'));

        $this->assertSame('Développeur', $experience->translate('en')?->getJobName());
    }

    public function testItReturnsNullWithoutAnyTranslation(): void
    {
        $this->assertNotInstanceOf(\App\CV\Domain\Data\ProfessionalExperienceTranslation::class, new ProfessionalExperience()->translate('en'));
    }
}
