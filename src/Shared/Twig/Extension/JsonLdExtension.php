<?php

namespace App\Shared\Twig\Extension;

use Twig\Extension\AbstractExtension;

final class JsonLdExtension extends AbstractExtension
{
    public function getFunctions(): iterable
    {
        yield new \Twig\TwigFunction('json_ld', [$this, 'jsonLd'], [
            'is_safe' => ['html'],
        ]);
    }

    public function jsonLd(array $data): string
    {
        return sprintf('<script type="application/ld+json">%s</script>', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
