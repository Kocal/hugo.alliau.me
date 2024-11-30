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
    'admin' => [
        'path' => './assets/admin.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.5',
    ],
    'leaflet' => [
        'version' => '1.9.4',
    ],
    'leaflet/dist/leaflet.min.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    '@symfony/ux-leaflet-map' => [
        'path' => './vendor/symfony/ux-leaflet-map/assets/dist/map_controller.js',
    ],
    'codemirror' => [
        'version' => '6.0.1',
    ],
    '@codemirror/lang-markdown' => [
        'version' => '6.3.0',
    ],
    '@codemirror/lang-sql' => [
        'version' => '6.8.0',
    ],
    '@codemirror/lang-css' => [
        'version' => '6.3.0',
    ],
    '@codemirror/lang-html' => [
        'version' => '6.4.9',
    ],
    '@codemirror/lang-yaml' => [
        'version' => '6.1.1',
    ],
    '@codemirror/lang-javascript' => [
        'version' => '6.2.2',
    ],
    '@codemirror/lang-vue' => [
        'version' => '0.1.3',
    ],
    '@codemirror/lang-sass' => [
        'version' => '6.0.2',
    ],
    '@codemirror/lang-php' => [
        'version' => '6.0.1',
    ],
    '@codemirror/lang-json' => [
        'version' => '6.0.1',
    ],
    '@codemirror/view' => [
        'version' => '6.32.0',
    ],
    '@codemirror/state' => [
        'version' => '6.4.1',
    ],
    '@codemirror/language' => [
        'version' => '6.10.2',
    ],
    '@codemirror/commands' => [
        'version' => '6.3.3',
    ],
    '@codemirror/search' => [
        'version' => '6.5.5',
    ],
    '@codemirror/autocomplete' => [
        'version' => '6.12.0',
    ],
    '@codemirror/lint' => [
        'version' => '6.4.2',
    ],
    '@lezer/markdown' => [
        'version' => '1.3.1',
    ],
    '@lezer/common' => [
        'version' => '1.2.1',
    ],
    '@lezer/highlight' => [
        'version' => '1.2.1',
    ],
    '@lezer/lr' => [
        'version' => '1.4.0',
    ],
    '@lezer/css' => [
        'version' => '1.1.8',
    ],
    '@lezer/html' => [
        'version' => '1.3.9',
    ],
    '@lezer/yaml' => [
        'version' => '1.0.2',
    ],
    '@lezer/javascript' => [
        'version' => '1.4.13',
    ],
    '@lezer/sass' => [
        'version' => '1.0.7',
    ],
    '@lezer/php' => [
        'version' => '1.0.2',
    ],
    '@lezer/json' => [
        'version' => '1.0.2',
    ],
    'style-mod' => [
        'version' => '4.1.2',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    'crelt' => [
        'version' => '1.0.6',
    ],
    '@codemirror/legacy-modes/mode/shell' => [
        'version' => '6.4.1',
    ],
    '@fsegurai/codemirror-theme-github-light' => [
        'version' => '6.0.2',
    ],
    '@fsegurai/codemirror-theme-github-dark' => [
        'version' => '6.0.2',
    ],
    'inter-ui/inter.css' => [
        'version' => '4.1.0',
        'type' => 'css',
    ],
    'inter-ui/inter-variable.css' => [
        'version' => '4.1.0',
        'type' => 'css',
    ],
];
