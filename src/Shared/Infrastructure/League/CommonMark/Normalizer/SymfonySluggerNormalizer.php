<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\League\CommonMark\Normalizer;

use League\CommonMark\Normalizer\TextNormalizerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Contrary to the original {@link \League\CommonMark\Normalizer\SlugNormalizer}, this implementation uses the Symfony
 * slugger to normalize text.
 * It converts accented characters to their ASCII equivalent, and makes anchors work.
 */
final readonly class SymfonySluggerNormalizer implements TextNormalizerInterface
{
    private AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
    }

    public function normalize(string $text, array $context = []): string
    {
        return $this->slugger->slug($text)->toString();
    }
}
