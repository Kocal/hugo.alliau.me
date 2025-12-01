<?php

declare(strict_types=1);

namespace App\CV\Domain\Repository;

use App\CV\Domain\Data\Project;

interface ProjectRepository
{
    public function save(Project $project): void;

    /**
     * @return array<Project>
     */
    public function findAllVisible(): array;

    public function findOneLatest(): Project|null;
}
