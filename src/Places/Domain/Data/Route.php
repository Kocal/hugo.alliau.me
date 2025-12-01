<?php

declare(strict_types=1);

namespace App\Places\Domain\Data;

enum Route: string
{
    case INDEX = 'app.places.view_list';
}
