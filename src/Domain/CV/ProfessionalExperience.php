<?php

namespace App\Domain\CV;

use App\Domain\CV\Repository\ProfessionalExperienceRepository;
use App\Domain\Routing\ValueObject\RouteName;
use App\Http\Cache\CacheableEntity;
use App\Http\Cache\ValueObject\CacheItem;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProfessionalExperienceRepository::class)]
#[ORM\Table(name: 'cv_professional_experience')]
#[ORM\HasLifecycleCallbacks]
class ProfessionalExperience implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $badges = [];

    public function getId(): ?int
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
     * @return string[]
     */
    public function getBadges(): array
    {
        return $this->badges;
    }

    /**
     * @param string[] $badges
     */
    public function setBadges(array $badges): static
    {
        $this->badges = $badges;

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

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getEtag(): string
    {
        return 'cv:professional_experience:' . $this->id . ':' . ($this->updatedAt?->format('U') ?? '0');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(RouteName::CV_HOME),
        ];
    }
}
