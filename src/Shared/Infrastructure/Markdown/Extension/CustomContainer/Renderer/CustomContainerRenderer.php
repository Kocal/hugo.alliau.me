<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Renderer;

use App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Node\CustomContainer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

final readonly class CustomContainerRenderer implements NodeRendererInterface
{
    public function __construct(
        private \Twig\Environment $twig,
    ) {
    }

    #[\Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! ($node instanceof CustomContainer)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . $node::class);
        }

        $type = $node->getType();

        return $this->twig->render('markdown/_custom_container.html.twig', [
            'type' => $type,
            'content' => $childRenderer->renderNodes($node->children()),
        ]);
    }
}
