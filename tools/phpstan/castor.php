<?php

namespace qa\phpstan;

use Castor\Attribute\AsTask;
use function Castor\exit_code;
use function Castor\run;

#[AsTask(description: 'Run PHPStan', aliases: ['phpstan'])]
function phpstan(bool $generateBaseline = false): int
{
    $command = [
        'symfony',
        'php',
        __DIR__ . '/vendor/bin/phpstan',
    ];

    if ($generateBaseline) {
        $command[] = '-b';
    }

    return exit_code($command);
}

#[AsTask(description: 'install dependencies')]
function install(): void
{
    run(['symfony', 'composer', 'install'], workingDirectory: __DIR__);
}

#[AsTask(description: 'update dependencies')]
function update(): void
{
    run(['symfony', 'composer', 'update'], workingDirectory: __DIR__);
    run(['symfony', 'composer', 'bump'], workingDirectory: __DIR__);
}
