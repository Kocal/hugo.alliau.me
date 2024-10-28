<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Markdown\Extension\GitHubEmojis\Parser;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Symfony\Component\Emoji\EmojiTransliterator;

final class GitHubEmojisInlineParser implements InlineParserInterface
{
    private EmojiTransliterator $emojiTransliterator;

    #[\Override]
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(':[a-z0-9\+\-_]+:');
    }

    #[\Override]
    public function parse(InlineParserContext $inlineContext): bool
    {
        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());

        $match = $inlineContext->getFullMatch();

        $inlineContext->getContainer()->appendChild(
            new Text($this->getTransliterator()->transliterate($match) ?: $match)
        );

        return true;
    }

    private function getTransliterator(): EmojiTransliterator
    {
        return $this->emojiTransliterator ??= EmojiTransliterator::create('text-emoji');
    }
}
