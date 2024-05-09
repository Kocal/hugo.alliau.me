<?php

namespace App\Places\Domain;

enum PlaceTag: string
{
    case RESTAURANT = 'restaurant';
    case CAFE = 'cafe';
    case HOTEL = 'hotel';
    case MUSEUM = 'museum';
    case PARK = 'park';
    case SHOPPING = 'shopping';
    case POINT_OF_VIEW = 'point_of_view';
    case POINT_OF_INTEREST = 'point_of_interest';
    case ACTIVITY = 'activity';
    case MARKET = 'market';
}