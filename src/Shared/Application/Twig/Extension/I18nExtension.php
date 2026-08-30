<?php

declare(strict_types=1);

namespace App\Shared\Application\Twig\Extension;

use App\Shared\Domain\Data\Locale;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class I18nExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getFunctions(): iterable
    {
        yield new TwigFunction('t_html', $this->tHtml(...), [
            'is_safe' => ['html'],
        ]);

        yield new TwigFunction('enabled_locales', $this->enabledLocales(...));
    }

    /**
     * @return list<Locale>
     */
    public function enabledLocales(): array
    {
        return Locale::cases();
    }

    /**
     * @param list<array<string, string>> $links
     * @param array<string, mixed> $parameters
     */
    public function tHtml(string $key, array $links = [], array $parameters = [], string $domain = 'messages'): string
    {
        $translated = $this->translator->trans($key, $parameters, $domain);

        return (string) preg_replace_callback(
            '#<(\d+)>(.*?)</\1>#',
            static function (array $matches) use ($links): string {
                $index = (int) $matches[1];
                $text = htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                $attributes = '';
                foreach ($links[$index] ?? [] as $name => $value) {
                    $attributes .= sprintf(' %s="%s"', $name, htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                }

                return sprintf('<a%s>%s</a>', $attributes, $text);
            },
            $translated,
        );
    }
}
