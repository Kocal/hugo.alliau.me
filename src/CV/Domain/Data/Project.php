<?php

declare(strict_types=1);

namespace App\CV\Domain\Data;

use App\Shared\Domain\Data\Locale;
use App\Shared\Domain\Data\ValueObject\ProjectId;
use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity()]
#[ORM\Table(name: 'cv_project')]
#[ORM\HasLifecycleCallbacks]
class Project implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'project_id')]
    private ProjectId $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $techStack = [];

    #[ORM\Column(options: [
        'default' => false,
    ])]
    private bool $visible = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<string, ProjectTranslation>
     */
    #[ORM\OneToMany(targetEntity: ProjectTranslation::class, mappedBy: 'project', cascade: [
        'persist',
        'remove',
    ], orphanRemoval: true, indexBy: 'locale')]
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    private Collection $translations;

    public function __construct()
    {
        $this->id = ProjectId::generate();
        $this->createdAt = Clock::get()->now();
        $this->updatedAt = $this->createdAt;
        $this->translations = new ArrayCollection();
    }

    public function getId(): ProjectId
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getTechStack(): array
    {
        return $this->techStack;
    }

    /**
     * @param array<string> $techStack
     */
    public function setTechStack(array $techStack): static
    {
        $this->techStack = array_values($techStack);

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

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

    /**
     * @return Collection<string, ProjectTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProjectTranslation $translation): static
    {
        if (! $this->translations->contains($translation)) {
            $this->translations->set($translation->getLocale()->value, $translation);
            $translation->setProject($this);
        }

        return $this;
    }

    public function removeTranslation(ProjectTranslation $translation): static
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    public function translate(string $locale): ?ProjectTranslation
    {
        return $this->translations[$locale]
            ?? $this->translations[Locale::EN->value]
            ?? $this->translations[Locale::FR->value];
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
