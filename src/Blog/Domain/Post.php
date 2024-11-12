<?php

declare(strict_types=1);

namespace App\Blog\Domain;

use App\Blog\Domain\Route as RouteBlog;
use App\Shared\Domain\Data\ValueObject\PostId;
use App\Shared\Domain\HttpCache\CacheableEntity;
use App\Shared\Domain\HttpCache\CacheItem;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity()]
#[ORM\Table(name: 'blog_post')]
#[ORM\Index(columns: ['status', 'published_at'])]
#[ORM\HasLifecycleCallbacks]
class Post implements CacheableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'post_id')]
    private PostId $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

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
        $this->id = PostId::generate();
        $this->seo = new PostSeo();
        $this->createdAt = Clock::get()->now();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): PostId
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

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getEtag(): string
    {
        return 'blog:post:' . $this->id . ':' . $this->updatedAt->format('U');
    }

    #[\Override]
    public function getCacheItems(): array
    {
        return [
            CacheItem::fromRoute(RouteBlog::HOME),
            CacheItem::fromRoute(RouteBlog::POST_VIEW, [
                'slug' => $this->slug,
            ]),
            CacheItem::fromRoute(RouteBlog::TAG_LIST),
            ...array_map(
                static fn (string $tag): \App\Shared\Domain\HttpCache\CacheItem =>
                CacheItem::fromRoute(RouteBlog::TAG_VIEW, [
                    'tag' => $tag,
                ]),
                $this->tags
            ),
            CacheItem::fromRoute(RouteBlog::RSS),
        ];
    }
}
