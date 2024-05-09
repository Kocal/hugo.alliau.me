<?php

namespace App\Places\Domain;

enum PlaceOrigin: string
{
    case IMPORTED = 'imported';
    case USER = 'user';
}