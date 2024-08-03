<?php

declare(strict_types=1);

namespace App\User\Domain;

enum Route: string
{
    case LOGIN = 'app.user.login';

    case LOGOUT = 'app.user.logout';
}
