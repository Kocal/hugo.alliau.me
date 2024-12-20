<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\FencedCode\Renderer;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use function Symfony\Component\String\s;

final class FencedCodeRenderer implements NodeRendererInterface
{
    #[\Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        FencedCode::assertInstanceOf($node);
        /** @var FencedCode $node */

        $matches = s($node->getInfo())->match('/^(?P<language>\w+)(?P<meta>.*)/');
        $language = $matches['language'];
        $meta = $matches['meta'] ?? '';

        $linesCount = count(explode("\n", trim($node->getLiteral())));
        $lines = range(1, $linesCount);

        return new HtmlElement(
            'div',
            [
                'class' => 'language no-prose',
                'data-controller' => 'code',
                'data-code-language-value' => $language,
                'data-code-meta-value' => $meta,
            ],
            [
                new HtmlElement('span', [
                    'class' => 'lang',
                ], $language),
                new HtmlElement(
                    'div',
                    [
                        'class' => 'lines-number-wrapper',
                    ],
                    array_map(
                        fn (int $line): string => new HtmlElement('span', [], (string) $line) . '<br>',
                        $lines
                    )
                ),
                new HtmlElement(
                    'pre',
                    [
                        'data-code-target' => 'pre',
                    ],
                    new HtmlElement(
                        'code',
                        [
                            'data-code-target' => 'code',
                        ],
                        Xml::escape($node->getLiteral())
                    )
                ),
                //new HtmlElement(
                //    'button',
                //    [
                //        'class' => 'copy',
                //        'data-action' => 'click->code#copy',
                //        'aria-label' => 'Copy code to clipboard',
                //    ],
                //    '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><g fill="currentColor"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/></g></svg>'
                //)
            ]
        );
    }
}
