<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown;

use App\Shared\Domain\Markdown\MarkdownConverter;
use App\Shared\Domain\Markdown\MarkdownDocument;
use App\Shared\Infrastructure\Markdown\Extension\CodeBlockHighlight\Renderer\CodeBlockRenderer;
use App\Shared\Infrastructure\Markdown\Extension\CustomContainer\CustomContainerExtension;
use App\Shared\Infrastructure\Markdown\Extension\GitHubEmojis\GitHubEmojisExtension;
use App\Shared\Infrastructure\Markdown\Normalizer\SymfonySluggerNormalizer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\Query;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;
use Psr\Link\LinkInterface;
use Symfony\Component\WebLink\Link;

final readonly class LeagueCommonMarkMarkdownConverter implements MarkdownConverter
{
    private Environment $environment;

    private MarkdownParser $parser;

    public function __construct()
    {
        $this->environment = new Environment([
            'heading_permalink' => [
                'apply_id_to_heading' => true,
                'symbol' => '​',
                'aria_hidden' => false,
            ],
            'slug_normalizer' => [
                'instance' => new SymfonySluggerNormalizer(),
            ],
        ]);
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->environment->addExtension(new HeadingPermalinkExtension());
        $this->environment->addExtension(new CustomContainerExtension());
        $this->environment->addExtension(new GitHubEmojisExtension());
        $this->environment->addExtension(new AttributesExtension());
        $this->environment->addRenderer(FencedCode::class, new CodeBlockRenderer());

        $this->parser = new MarkdownParser($this->environment);
    }

    #[\Override]
    public function __invoke(string $input): MarkdownDocument
    {
        $document = $this->parser->parse($input);

        $htmlRenderer = new HtmlRenderer($this->environment);

        return new MarkdownDocument(
            renderedContent: $htmlRenderer->renderNodes([$document]),
            webLinks: $this->getWebLinks($document),
        );
    }

    /**
     * @return list<LinkInterface>
     */
    private function getWebLinks(Document $document): array
    {
        $webLinks = [];

        $imagesWithHighPriority = new Query()
            ->where(fn (Node $node): bool => $node instanceof Image && $node->data->get('attributes.fetchpriority', '') === 'high')
            ->findAll($document);

        foreach ($imagesWithHighPriority as $image) {
            if (! $image instanceof Image) {
                continue;
            }

            $webLinks[] = new Link()
                ->withHref($image->getUrl())
                ->withAttribute('as', 'image')
                ->withAttribute('fetchpriority', 'high');
        }

        return $webLinks;
    }
}
