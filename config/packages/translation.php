<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Shared\Domain\Data\Locale;

return App::config([
    'framework' => [
        'default_locale' => Locale::default()->value,
        'enabled_locales' => array_map(static fn (Locale $locale): string => $locale->value, Locale::cases()),
        'translator' => [
            'default_path' => '%kernel.project_dir%/translations',
            'fallbacks' => [Locale::default()->value],
        ],
    ],
]);
