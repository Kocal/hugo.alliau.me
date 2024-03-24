<?php

namespace App\Shared\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter as CommonMarkMarkdownConverter;

class MarkdownConverter {

    private CommonMarkMarkdownConverter  $converter; 

    public function __construct()
    {
        $environment = new Environment([
            'heading_permalink' => [
                'insert' => 'after',
            ]
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        $this->converter = new CommonMarkMarkdownConverter($environment);
    }

    public function __invoke(string $input): string
    {
        return $this->converter->convert($input);
    }
}