<?php

declare(strict_types=1);

namespace App\Blog\Domain;

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

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $dependencies = [];

    /**
     * @var value-of<self::PROFICIENCY_LEVEL>|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Choice(choices: self::PROFICIENCY_LEVEL)]
    private ?string $proficiencyLevel = null;

    /**
     * @return list<string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param array<string> $dependencies
     */
    public function setDependencies(array $dependencies): static
    {
        $this->dependencies = array_values($dependencies);

        return $this;
    }

    public function getProficiencyLevel(): ?string
    {
        return $this->proficiencyLevel;
    }

    /**
     * @param value-of<self::PROFICIENCY_LEVEL>|null $proficiencyLevel
     */
    public function setProficiencyLevel(string|null $proficiencyLevel): static
    {
        $this->proficiencyLevel = $proficiencyLevel;

        return $this;
    }
}
