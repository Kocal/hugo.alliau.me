<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

require __DIR__ . '/../vendor/autoload.php';

new Dotenv()
    ->bootEnv(__DIR__ . '/../.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

set_exception_handler([new ErrorHandler(), 'handleException']);
