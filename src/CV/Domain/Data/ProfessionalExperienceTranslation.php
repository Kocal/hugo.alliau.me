<?php

declare(strict_types=1);

namespace App\CV\Domain\Data;

use App\Shared\Domain\Data\Locale;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'cv_professional_experience_translation')]
#[ORM\UniqueConstraint(columns: ['experience_id', 'locale'])]
class ProfessionalExperienceTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProfessionalExperience $experience = null;

    #[ORM\Column(length: 5, enumType: Locale::class)]
    private Locale $locale = Locale::EN;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $jobName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $badges = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExperience(): ?ProfessionalExperience
    {
        return $this->experience;
    }

    public function setExperience(?ProfessionalExperience $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getJobName(): ?string
    {
        return $this->jobName;
    }

    public function setJobName(string $jobName): static
    {
        $this->jobName = $jobName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getBadges(): array
    {
        return $this->badges;
    }

    /**
     * @param array<string> $badges
     */
    public function setBadges(array $badges): static
    {
        $this->badges = array_values($badges);

        return $this;
    }
}
