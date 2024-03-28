<?php

namespace App\Shared\Markdown\Extension\CustomContainer\Parser;

use App\Shared\Markdown\Extension\CustomContainer\Node\CustomContainer;
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
    private CustomContainer $customContainer;
    private bool $finished = false;

    public static function createBlockStartParser(): BlockStartParserInterface
    {
        return new class implements BlockStartParserInterface {
            public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
            {
                if ($cursor->isIndented()) {
                    return BlockStart::none();
                }

                if (null === $start = $cursor->match('/^::: \w+/')) {
                    return BlockStart::none();
                }

                $matches = s($start)->match('/^::: (?P<type>\w+)(?: (?P<title>.+))?$/');

                return BlockStart::of(new CustomContainerBlockParser(
                    $matches['type'],
                    $matches['title'] ?? $matches['type']
                ))
                    ->at($cursor);
            }
        };
    }

    public function __construct(
        string $type,
        string|null $title
    ) {
        $this->customContainer = new CustomContainer($type, $title);
    }

    public function getBlock(): AbstractBlock
    {
        return $this->customContainer;
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        if ($this->finished) {
            return BlockContinue::none();
        }

        if ($cursor->isBlank()) {
            return BlockContinue::at($cursor);
        }

        if (null !== $cursor->match('/^:::$/')) {
            $this->finished = true;
        }


        return BlockContinue::at($cursor);
    }
}