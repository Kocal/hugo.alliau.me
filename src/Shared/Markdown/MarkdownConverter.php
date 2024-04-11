<?php

namespace App\Shared\Markdown;

use App\Shared\Markdown\Extension\CustomContainer\CustomContainerExtension;
use App\Shared\Markdown\Extension\FencedCode\Renderer\FencedCodeRenderer;
use App\Shared\Markdown\Extension\GitHubEmojis\GitHubEmojisExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\Node\TableOfContents;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\Query;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;
use Psr\Link\LinkInterface;
use Symfony\Component\WebLink\Link;

class MarkdownConverter
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
        ]);
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->environment->addExtension(new HeadingPermalinkExtension());
        $this->environment->addExtension(new TableOfContentsExtension());
        $this->environment->addExtension(new CustomContainerExtension());
        $this->environment->addExtension(new GitHubEmojisExtension());
        $this->environment->addExtension(new AttributesExtension());
        $this->environment->addRenderer(FencedCode::class, new FencedCodeRenderer());

        $this->parser = new MarkdownParser($this->environment);
    }

    /**
     * @return array{ rendered_content: string, rendered_toc: null|string }
     */
    public function __invoke(string $input): array
    {
        $document = $this->parser->parse($input);

        $htmlRenderer = new HtmlRenderer($this->environment);

        return [
            'rendered_content' => $htmlRenderer->renderNodes([$document]),
            'rendered_toc' => ($toc = $this->extractToc($document)) ? $htmlRenderer->renderNodes([$toc]) : null,
            'web_links' => $this->getWebLinks($document),
        ];
    }

    private function extractToc(Document $document): Node|null
    {
        $toc = (new Query())
            ->where(Query::type(TableOfContents::class))
            ->findOne($document);

        if ($toc) {
            $toc->detach();
            return $toc;
        }

        return null;
    }

    /**
     * @return list<LinkInterface>
     */
    private function getWebLinks(Document $document): array
    {
        $webLinks = [];

        $imagesWithHighPriority = (new Query())
            ->where(function(Node $node) {
                return $node instanceof Image && $node->data->get('attributes.fetchpriority', '') === 'high';
            })
            ->findAll($document)
        ;

        foreach ($imagesWithHighPriority as $image) {
            $webLinks[] = (new Link())
                ->withHref($image->getUrl())
                ->withAttribute('as', 'image')
                ->withAttribute('fetchpriority', 'high')
            ;
        }

        return $webLinks;
    }
}
