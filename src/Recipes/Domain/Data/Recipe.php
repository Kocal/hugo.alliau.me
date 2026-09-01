<?php

declare(strict_types=1);

namespace App\Recipes\Domain\Data;

use App\Shared\Domain\Data\Locale;
use App\Shared\Domain\Data\ValueObject\RecipeId;
use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'recipe')]
#[ORM\Index(columns: ['locale', 'visible'])]
#[ORM\HasLifecycleCallbacks]
class Recipe implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'recipe_id')]
    private RecipeId $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private RecipeType $type = RecipeType::MAIN;

    #[ORM\Column(length: 5, options: [
        'default' => Locale::FR,
    ])]
    private Locale $locale = Locale::FR;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::SMALLINT, options: [
        'default' => 4,
    ])]
    #[Assert\Positive]
    private int $servings = 4;

    #[ORM\Column(type: 'recipe_content')]
    private RecipeContent $content;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $sourceLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $sourceUrl = null;

    #[ORM\Column(options: [
        'default' => false,
    ])]
    private bool $visible = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = RecipeId::generate();
        $this->content = new RecipeContent();
        $this->createdAt = Clock::get()->now();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): RecipeId
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): RecipeType
    {
        return $this->type;
    }

    public function setType(RecipeType $type): static
    {
        $this->type = $type;

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

    public function getServings(): int
    {
        return $this->servings;
    }

    public function setServings(int $servings): static
    {
        $this->servings = $servings;

        return $this;
    }

    public function getContent(): RecipeContent
    {
        return $this->content;
    }

    public function setContent(RecipeContent $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSourceLabel(): ?string
    {
        return $this->sourceLabel;
    }

    public function setSourceLabel(?string $sourceLabel): static
    {
        $this->sourceLabel = $sourceLabel;

        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): static
    {
        $this->sourceUrl = $sourceUrl;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
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
        return 'recipes:recipe:' . $this->id . ':' . $this->updatedAt->format('U');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(Route::LIST),
            CacheItem::fromRoute(Route::VIEW, [
                'slug' => $this->slug,
            ]),
        ];
    }
}
