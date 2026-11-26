<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Twig\Component;

use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Twig\Environment;

#[CoversNothing]
final class VariantClassesTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    public function testBadgeRendersItsColorAndSizeVariants(): void
    {
        $html = (string) $this->renderTwigComponent('Badge', [], 'PHP');

        $this->assertStringContainsString('c_accent.fg', $html);
        $this->assertStringContainsString('px_2', $html);
    }

    public function testBadgeWithHrefRendersItsHoverVariant(): void
    {
        $html = (string) $this->renderTwigComponent('Badge', [
            'href' => '/blog/tags/php',
        ], 'PHP');

        $this->assertStringContainsString('hover:bg-c_accent/20', $html);
    }

    public function testBadgeKeepsTheClassOfItsCaller(): void
    {
        $html = (string) $this->renderTwigComponent('Badge', [
            'class' => 'from-caller',
        ], 'PHP');

        $this->assertStringContainsString('from-caller', $html);
        $this->assertStringContainsString('c_accent.fg', $html);
    }

    public function testCustomContainerRendersItsTypeVariant(): void
    {
        $twig = self::getContainer()->get(Environment::class);

        $html = $twig->render('components/CustomContainer.html.twig', [
            'type' => 'warning',
            'content' => '<p>Careful</p>',
        ]);

        $this->assertStringContainsString('bd-l-c_yellow.600', $html);
    }
}
