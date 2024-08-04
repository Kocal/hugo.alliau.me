<?php

declare(strict_types=1);

namespace App\Shared\Twig\Extension;

use Twig\Extension\AbstractExtension;

final class JsonLdExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): iterable
    {
        yield new \Twig\TwigFunction('json_ld', $this->jsonLd(...), [
            'is_safe' => ['html'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function jsonLd(array $data): string
    {
        return sprintf('<script type="application/ld+json">%s</script>', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
