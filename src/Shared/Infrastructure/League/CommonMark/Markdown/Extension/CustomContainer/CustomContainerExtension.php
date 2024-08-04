<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\CustomContainer;

use App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\CustomContainer\Node\CustomContainer;
use App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\CustomContainer\Parser\CustomContainerBlockParser;
use App\Shared\Infrastructure\League\CommonMark\Markdown\Extension\CustomContainer\Renderer\CustomContainerRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * @see https://vitepress.dev/guide/markdown#custom-containers
 */
final class CustomContainerExtension implements ExtensionInterface
{
    #[\Override]
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(CustomContainerBlockParser::createBlockStartParser())
            ->addRenderer(CustomContainer::class, new CustomContainerRenderer())
        ;
    }
}
