<?php

use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\run;
use function Castor\io;

import(__DIR__ . '/.castor');

#[AsTask(description: 'Installs the application', namespace: 'app', aliases: ['install'])]
function install(): void
{
    io()->title('Installing the application');

    io()->section('Installing PHP dependencies');
    run('symfony composer install --no-interaction --prefer-dist');
}