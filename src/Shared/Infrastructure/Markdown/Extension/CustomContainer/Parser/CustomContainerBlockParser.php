<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Parser;

use App\Shared\Infrastructure\Markdown\Extension\CustomContainer\Node\CustomContainer;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use function Symfony\Component\String\s;

final class CustomContainerBlockParser extends AbstractBlockContinueParser
{
    private readonly CustomContainer $customContainer;

    private bool $finished = false;

    public function __construct(
        string $type,
        string|null $title
    ) {
        $this->customContainer = new CustomContainer($type, $title);
    }

    public static function createBlockStartParser(): BlockStartParserInterface
    {
        return new class() implements BlockStartParserInterface {
            public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
            {
                if ($cursor->isIndented()) {
                    return BlockStart::none();
                }

                if (null === $start = $cursor->match('/^::: .+/')) {
                    return BlockStart::none();
                }

                $matches = s($start)->match('/^::: (?P<type>\w+)(?: (?P<title>.+))?/');

                return BlockStart::of(new CustomContainerBlockParser(
                    $matches['type'],
                    $matches['title'] ?? $matches['type']
                ))
                    ->at($cursor);
            }
        };
    }

    #[\Override]
    public function getBlock(): AbstractBlock
    {
        return $this->customContainer;
    }

    #[\Override]
    public function isContainer(): bool
    {
        return true;
    }

    #[\Override]
    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    #[\Override]
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        if ($this->finished) {
            return BlockContinue::none();
        }

        if ($cursor->isBlank()) {
            return BlockContinue::at($cursor);
        }

        if ($cursor->match('/^:::$/') !== null) {
            $this->finished = true;
        }

        return BlockContinue::at($cursor);
    }
}
