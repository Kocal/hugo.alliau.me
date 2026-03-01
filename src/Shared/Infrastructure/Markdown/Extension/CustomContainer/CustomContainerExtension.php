<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\CustomContainer;

use App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Node\CustomContainer;
use App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Parser\CustomContainerBlockParser;
use App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Renderer\CustomContainerRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * @see https://vitepress.dev/guide/markdown#custom-containers
 */
final readonly class CustomContainerExtension implements ExtensionInterface
{
    public function __construct(
        private \Twig\Environment $twig,
    ) {
    }

    #[\Override]
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(CustomContainerBlockParser::createBlockStartParser())
            ->addRenderer(CustomContainer::class, new CustomContainerRenderer($this->twig))
        ;
    }
}
