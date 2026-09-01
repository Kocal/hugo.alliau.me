<?php

declare(strict_types=1);

namespace App\Shared\Application\Twig\Extension;

use App\Shared\Domain\Data\Locale;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class I18nExtension extends AbstractExtension
{
    /**
     * @param list<array{route: string, fallback_route: string}> $singleLocaleRoutes
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire(param: 'app.single_locale_routes')]
        private readonly array $singleLocaleRoutes = [],
    ) {
    }

    #[\Override]
    public function getFunctions(): iterable
    {
        yield new TwigFunction('t_html', $this->tHtml(...), [
            'is_safe' => ['html'],
        ]);

        yield new TwigFunction('enabled_locales', $this->enabledLocales(...));

        yield new TwigFunction('single_locale_fallback_route', $this->singleLocaleFallbackRoute(...));
    }

    /**
     * @return list<Locale>
     */
    public function enabledLocales(): array
    {
        return Locale::cases();
    }

    /**
     * Returns the route a language switcher should target when $route only exists under one
     * locale, or null when $route exists under every locale.
     */
    public function singleLocaleFallbackRoute(string $route): ?string
    {
        foreach ($this->singleLocaleRoutes as $entry) {
            if ($entry['route'] === $route) {
                return $entry['fallback_route'];
            }
        }

        return null;
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
