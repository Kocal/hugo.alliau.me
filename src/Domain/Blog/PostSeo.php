<?php

namespace App\Domain\Blog;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class PostSeo
{
    /**
     * Valid "proficiencyLevel" enum values from BlogPost (schema.org).
     */
    public const PROFICIENCY_LEVEL = [
        'Beginner',
        'Intermediate',
        'Expert',
    ];

    #[ORM\Column(type: Types::JSON)]
    private array $dependencies = [];

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: self::PROFICIENCY_LEVEL)]
    private ?string $proficiencyLevel = null;

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function setDependencies(array $dependencies): static
    {
        $this->dependencies = $dependencies;

        return $this;
    }

    public function getProficiencyLevel(): ?string
    {
        return $this->proficiencyLevel;
    }

    public function setProficiencyLevel(string $proficiencyLevel): static
    {
        $this->proficiencyLevel = $proficiencyLevel;

        return $this;
    }
}
