<?php

namespace App\Shared\Markdown\Extension\FencedCode\Renderer;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use function Symfony\Component\String\s;

final class FencedCodeRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        FencedCode::assertInstanceOf($node);

        $matches = s($node->getInfo())->match('/^(?P<language>\w+)(?P<meta>.*)/');

        $attrs = $node->data->getData('attributes');
        $attrs->set('data-controller', 'code');
        $attrs->set('data-language', $matches['language']);
        $attrs->set('data-meta', $matches['meta'] ?? '');

        return new HtmlElement(
            'pre',
            [
                'class' => 'no-prose',
            ],
            new HtmlElement('code', $attrs->export(), Xml::escape($node->getLiteral()))
        );
    }
}