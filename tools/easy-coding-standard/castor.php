<?php

namespace qa\ecs;

use Castor\Attribute\AsTask;
use function Castor\exit_code;
use function Castor\run;

#[AsTask(description: 'Run EasyCodingStandard')]
function ecs(bool $dryRun = false): int
{
    $command = [
        'symfony',
        'php',
        __DIR__ . '/vendor/bin/ecs',
        'check',
    ];

    if (! $dryRun) {
        $command[] = '--fix';
    }

    return exit_code($command);
}

#[AsTask(description: 'install dependencies')]
function install(): void
{
    run(['symfony', 'composer', 'install'], workingDirectory: __DIR__);
}

#[AsTask(description: 'Update dependencies')]
function update(): void
{
    run(['symfony', 'composer', 'update'], workingDirectory: __DIR__);
    run(['symfony', 'composer', 'bump'], workingDirectory: __DIR__);
}
