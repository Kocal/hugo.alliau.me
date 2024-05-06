<?php

namespace App\Blog\Domain;

use App\Blog\Domain\Repository\PostRepository;
use App\Routing\Domain\ValueObject\RouteName;
use App\Shared\Http\Cache\CacheableEntity;
use App\Shared\Http\Cache\ValueObject\CacheItem;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'blog_post')]
#[ORM\Index(columns: ['status', 'published_at'])]
#[ORM\HasLifecycleCallbacks]
class Post implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, options: [
        'jsonb' => true,
    ])]
    private array $tags = [];

    #[ORM\Embedded(class: PostSeo::class)]
    #[Assert\Valid]
    private PostSeo $seo;

    #[ORM\Column(options: [
        'default' => PostStatus::DRAFT,
    ])]
    private PostStatus $status = PostStatus::DRAFT;

    public function __construct()
    {
        $this->seo = new PostSeo();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = array_values($tags);

        return $this;
    }

    public function setSeo(PostSeo $seo): static
    {
        $this->seo = $seo;

        return $this;
    }

    public function getSeo(): PostSeo
    {
        return $this->seo;
    }

    public function setStatus(PostStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): PostStatus
    {
        return $this->status;
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
        return 'blog:post:' . $this->id . ':' . ($this->updatedAt?->format('U') ?? '0');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(RouteName::BLOG_HOME),
            CacheItem::fromRoute(RouteName::BLOG_POST_VIEW, [
                'slug' => $this->slug,
            ]),
            CacheItem::fromRoute(RouteName::BLOG_TAG_LIST),
            ...array_map(static fn (string $tag) => CacheItem::fromRoute(RouteName::BLOG_TAG_VIEW, [
                'tag' => $tag,
            ]), $this->tags),
            CacheItem::fromRoute(RouteName::BLOG_RSS),
        ];
    }
}