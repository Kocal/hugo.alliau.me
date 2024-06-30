<?php

declare(strict_types=1);

namespace App\CV\Domain\Repository;

use App\CV\Domain\Project;

interface ProjectRepository
{
    public function save(Project $project): void;

    /**
     * @return array<Project>
     */
    public function findAll(): array;

    public function findOneLatest(): Project|null;
}
