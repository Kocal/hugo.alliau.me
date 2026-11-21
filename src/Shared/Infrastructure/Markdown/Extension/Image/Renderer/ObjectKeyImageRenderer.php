<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\Image\Renderer;

use App\Shared\Infrastructure\Markdown\Extension\Image\Exception\MissingImageDimensionsException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeIterator;
use League\CommonMark\Node\StringContainerInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Symfony\UX\Image\Renderer\ImageRendererInterface;
use Symfony\UX\Image\Renderer\RenderOptions;
use Symfony\UX\Image\Twig\ImageMarkup;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

final class ObjectKeyImageRenderer implements NodeRendererInterface
{
    public const int CONTENT_WIDTH = 768;

    public const string PRESET = 'post';

    /**
     * @var list<array<string, mixed>>
     */
    private array $priorityImages = [];

    public function __construct(
        private readonly ImageRendererInterface $imageRenderer,
        private readonly Environment $twig,
    ) {
    }

    public static function isObjectKey(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }

        if (preg_match('{^(?:[a-z][a-z0-9+.-]*:|/)}i', $url) === 1) {
            return false;
        }

        return array_intersect(explode('/', $url), ['.', '..']) === [];
    }

    #[\Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?string
    {
        if (! $node instanceof Image) {
            throw new \InvalidArgumentException(\sprintf('Expected an instance of "%s", got "%s".', Image::class, $node::class));
        }

        $url = $node->getUrl();
        if (! self::isObjectKey($url)) {
            return null;
        }

        /** @var array<string, string|bool|int|float|null> $attributes */
        $attributes = $node->data->get('attributes', []);
        $width = $this->positiveInt($attributes['width'] ?? null);
        $height = $this->positiveInt($attributes['height'] ?? null);
        if ($width === null || $height === null) {
            throw MissingImageDimensionsException::forUrl($url);
        }

        $priority = 'high' === ($attributes['fetchpriority'] ?? null);
        unset($attributes['width'], $attributes['height'], $attributes['fetchpriority']);

        $rendered = $this->imageRenderer->render($url, $this->getAltText($node), new RenderOptions(
            width: $width,
            height: $height,
            priority: $priority,
            preset: self::PRESET,
        ));

        $extra = [
            'sizes' => \sprintf('(min-width: %1$dpx) %1$dpx, 100vw', min($width, self::CONTENT_WIDTH)),
        ];
        if (null !== $title = $node->getTitle()) {
            $extra['title'] = $title;
        }

        if ($priority) {
            $this->priorityImages[] = [
                ...$rendered->imgAttributes,
                'sizes' => $extra['sizes'],
            ];
        }

        return ImageMarkup::img($rendered, new ComponentAttributes(
            [...$extra, ...$attributes],
            $this->twig->getRuntime(EscaperRuntime::class),
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function drainPriorityImages(): array
    {
        $images = $this->priorityImages;
        $this->priorityImages = [];

        return $images;
    }

    private function positiveInt(mixed $value): ?int
    {
        if (! is_numeric($value) || (int) $value < 1) {
            return null;
        }

        return (int) $value;
    }

    private function getAltText(Image $node): string
    {
        $altText = '';

        foreach (new NodeIterator($node) as $n) {
            if ($n instanceof StringContainerInterface) {
                $altText .= $n->getLiteral();
            } elseif ($n instanceof Newline) {
                $altText .= "\n";
            }
        }

        return $altText;
    }
}
