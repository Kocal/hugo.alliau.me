<?php

namespace App\Shared\Markdown;

use App\Shared\Markdown\Extension\CustomContainer\CustomContainerExtension;
use App\Shared\Markdown\Extension\FencedCode\Renderer\FencedCodeRenderer;
use App\Shared\Markdown\Extension\GitHubEmojis\GitHubEmojisExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\Node\TableOfContents;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Node\Query;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

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

        $toc = (new Query())
            ->where(Query::type(TableOfContents::class))
            ->findOne($document);

        if ($toc) {
            $toc->detach();
        }

        $htmlRenderer = new HtmlRenderer($this->environment);

        return [
            'rendered_content' => $htmlRenderer->renderNodes([$document]),
            'rendered_toc' => $toc ? $htmlRenderer->renderNodes([$toc]) : null,
        ];
    }
}
