<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'shiki' => [
        'version' => '1.2.1',
    ],
    '@shikijs/core' => [
        'version' => '1.2.1',
    ],
    'shiki/langs/javascript.mjs' => [
        'version' => '1.2.1',
    ],
    'shiki/langs/php.mjs' => [
        'version' => '1.2.1',
    ],
    'shiki/langs/shell.mjs' => [
        'version' => '1.2.1',
    ],
    'shiki/langs/yaml.mjs' => [
        'version' => '1.2.1',
    ],
    'shiki/langs/bash.mjs' => [
        'version' => '1.2.1',
    ],
];
