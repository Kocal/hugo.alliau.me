<?php

namespace qa;

use Castor\Attribute\AsTask;

use function Castor\io;
use function Castor\import;

import(__DIR__ . '/../tools/easy-coding-standard/castor.php');
import(__DIR__ . '/../tools/phpstan/castor.php');

#[AsTask(description: 'Runs all QA tasks')]
function all(): int
{
    install();
    $cs = \qa\ecs\ecs();
    $phpstan = \qa\phpstan\phpstan();
    // $phpunit = phpunit();

    return max($cs, $phpstan/* , $phpunit */);
}

#[AsTask(description: 'Installs tooling')]
function install(): void
{
    io()->title('Installing QA tooling');

    \qa\ecs\install();
    \qa\phpstan\install();
}
