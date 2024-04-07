<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/assets',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tools',
    ])
    ->withSkip([
        __DIR__ . '/config/bundles.php',
    ])
    ->withPreparedSets(
        psr12: true,
        arrays: true,
        namespaces: true,
        spaces: true,
        docblocks: true,
        comments: true,
        cleanCode: true,
    );
