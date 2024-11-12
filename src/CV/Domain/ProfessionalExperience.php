<?php

declare(strict_types=1);

namespace App\CV\Domain;

use App\Shared\Domain\Data\ValueObject\ProfessionalExperienceId;
use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity()]
#[ORM\Table(name: 'cv_professional_experience')]
#[ORM\HasLifecycleCallbacks]
class ProfessionalExperience implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'professional_experience_id')]
    private ProfessionalExperienceId $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Url()]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $jobName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank()]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $badges = [];

    public function __construct()
    {
        $this->id = ProfessionalExperienceId::generate();
        $this->createdAt = Clock::get()->now();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ProfessionalExperienceId
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getEtag(): string
    {
        return 'cv:professional_experience:' . $this->id . ':' . $this->updatedAt->format('U');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(Route::INDEX),
        ];
    }
}
