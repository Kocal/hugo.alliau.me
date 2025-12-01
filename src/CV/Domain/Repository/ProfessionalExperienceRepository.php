<?php

declare(strict_types=1);

namespace App\CV\Domain\Repository;

use App\CV\Domain\Data\ProfessionalExperience;

interface ProfessionalExperienceRepository
{
    public function save(ProfessionalExperience $professionalExperience): void;

    /**
     * @return array<ProfessionalExperience>
     */
    public function findAll(): array;

    public function findOneLatest(): ProfessionalExperience|null;
}
