<?php

declare(strict_types=1);

namespace App\Places\Domain;

use Symfony\Component\Translation\TranslatableMessage;
use function Symfony\Component\Translation\t;

/**
 * https://developers.google.com/maps/documentation/places/web-service/place-types?hl=fr
 */
enum PlaceType: string
{
    case ADMINISTRATIVE_AREA_LEVEL_1 = 'administrative_area_level_1';
    case ADMINISTRATIVE_AREA_LEVEL_2 = 'administrative_area_level_2';
    case ADMINISTRATIVE_AREA_LEVEL_3 = 'administrative_area_level_3';
    case ADMINISTRATIVE_AREA_LEVEL_4 = 'administrative_area_level_4';
    case ADMINISTRATIVE_AREA_LEVEL_5 = 'administrative_area_level_5';
    case ADMINISTRATIVE_AREA_LEVEL_6 = 'administrative_area_level_6';
    case ADMINISTRATIVE_AREA_LEVEL_7 = 'administrative_area_level_7';
    case ARCHIPELAGO = 'archipelago';
    case COLLOQUIAL_AREA = 'colloquial_area';
    case CONTINENT = 'continent';
    case COUNTRY = 'country';
    case ESTABLISHMENT = 'establishment';
    case FINANCE = 'finance';
    case FLOOR = 'floor';
    case FOOD = 'food';
    case GENERAL_CONTRACTOR = 'general_contractor';
    case GEOCODE = 'geocode';
    case HEALTH = 'health';
    case INTERSECTION = 'intersection';
    case LANDMARK = 'landmark';
    case LOCALITY = 'locality';
    case NATURAL_FEATURE = 'natural_feature';
    case NEIGHBORHOOD = 'neighborhood';
    case PLACE_OF_WORSHIP = 'place_of_worship';
    case PLUS_CODE = 'plus_code';
    case POINT_OF_INTEREST = 'point_of_interest';
    case POLITICAL = 'political';
    case POST_BOX = 'post_box';
    case POSTAL_CODE = 'postal_code';
    case POSTAL_CODE_PREFIX = 'postal_code_prefix';
    case POSTAL_CODE_SUFFIX = 'postal_code_suffix';
    case POSTAL_TOWN = 'postal_town';
    case PREMISE = 'premise';
    case ROOM = 'room';
    case ROUTE = 'route';
    case STREET_ADDRESS = 'street_address';
    case STREET_NUMBER = 'street_number';
    case SUBLOCALITY = 'sublocality';
    case SUBLOCALITY_LEVEL_1 = 'sublocality_level_1';
    case SUBLOCALITY_LEVEL_2 = 'sublocality_level_2';
    case SUBLOCALITY_LEVEL_3 = 'sublocality_level_3';
    case SUBLOCALITY_LEVEL_4 = 'sublocality_level_4';
    case SUBLOCALITY_LEVEL_5 = 'sublocality_level_5';
    case SUBPREMISE = 'subpremise';
    case TOWN_SQUARE = 'town_square';

    // Culture
    case ART_GALLERY = 'art_gallery';
    case MUSEUM = 'museum';
    case PERFORMING_ARTS_THEATER = 'performing_arts_theater';

    // Education
    case LIBRARY = 'library';
    case PRESCHOOL = 'preschool';
    case PRIMARY_SCHOOL = 'primary_school';
    case SCHOOL = 'school';
    case SECONDARY_SCHOOL = 'secondary_school';
    case UNIVERSITY = 'university';

    // Entertainment and Recreation
    case AMUSEMENT_CENTER = 'amusement_center';
    case AMUSEMENT_PARK = 'amusement_park';
    case AQUARIUM = 'aquarium';
    case BANQUET_HALL = 'banquet_hall';
    case BOWLING_ALLEY = 'bowling_alley';
    case CASINO = 'casino';
    case COMMUNITY_CENTER = 'community_center';
    case CONVENTION_CENTER = 'convention_center';
    case CULTURAL_CENTER = 'cultural_center';
    case DOG_PARK = 'dog_park';
    case EVENT_VENUE = 'event_venue';
    case HIKING_AREA = 'hiking_area';
    case HISTORICAL_LANDMARK = 'historical_landmark';
    case MARINA = 'marina';
    case MOVIE_RENTAL = 'movie_rental';
    case MOVIE_THEATER = 'movie_theater';
    case NATIONAL_PARK = 'national_park';
    case NIGHT_CLUB = 'night_club';
    case PARK = 'park';
    case TOURIST_ATTRACTION = 'tourist_attraction';
    case VISITOR_CENTER = 'visitor_center';
    case WEDDING_VENUE = 'wedding_venue';
    case ZOO = 'zoo';

    // Food and Drink
    case AMERICAN_RESTAURANT = 'american_restaurant';
    case BAKERY = 'bakery';
    case BAR = 'bar';
    case BARBECUE_RESTAURANT = 'barbecue_restaurant';
    case BRAZILIAN_RESTAURANT = 'brazilian_restaurant';
    case BREAKFAST_RESTAURANT = 'breakfast_restaurant';
    case BRUNCH_RESTAURANT = 'brunch_restaurant';
    case CAFE = 'cafe';
    case CHINESE_RESTAURANT = 'chinese_restaurant';
    case COFFEE_SHOP = 'coffee_shop';
    case FAST_FOOD_RESTAURANT = 'fast_food_restaurant';
    case FRENCH_RESTAURANT = 'french_restaurant';
    case GREEK_RESTAURANT = 'greek_restaurant';
    case HAMBURGER_RESTAURANT = 'hamburger_restaurant';
    case ICE_CREAM_SHOP = 'ice_cream_shop';
    case INDIAN_RESTAURANT = 'indian_restaurant';
    case INDONESIAN_RESTAURANT = 'indonesian_restaurant';
    case ITALIAN_RESTAURANT = 'italian_restaurant';
    case JAPANESE_RESTAURANT = 'japanese_restaurant';
    case KOREAN_RESTAURANT = 'korean_restaurant';
    case LEBANESE_RESTAURANT = 'lebanese_restaurant';
    case MEAL_DELIVERY = 'meal_delivery';
    case MEAL_TAKEAWAY = 'meal_takeaway';
    case MEDITERRANEAN_RESTAURANT = 'mediterranean_restaurant';
    case MEXICAN_RESTAURANT = 'mexican_restaurant';
    case MIDDLE_EASTERN_RESTAURANT = 'middle_eastern_restaurant';
    case PIZZA_RESTAURANT = 'pizza_restaurant';
    case RAMEN_RESTAURANT = 'ramen_restaurant';
    case RESTAURANT = 'restaurant';
    case SANDWICH_SHOP = 'sandwich_shop';
    case SEAFOOD_RESTAURANT = 'seafood_restaurant';
    case SPANISH_RESTAURANT = 'spanish_restaurant';
    case STEAK_HOUSE = 'steak_house';
    case SUSHI_RESTAURANT = 'sushi_restaurant';
    case THAI_RESTAURANT = 'thai_restaurant';
    case TURKISH_RESTAURANT = 'turkish_restaurant';
    case VEGAN_RESTAURANT = 'vegan_restaurant';
    case VEGETARIAN_RESTAURANT = 'vegetarian_restaurant';
    case VIETNAMESE_RESTAURANT = 'vietnamese_restaurant';

    // Lodging
    case BED_AND_BREAKFAST = 'bed_and_breakfast';
    case CAMPGROUND = 'campground';
    case CAMPING_CABIN = 'camping_cabin';
    case COTTAGE = 'cottage';
    case EXTENDED_STAY_HOTEL = 'extended_stay_hotel';
    case FARMSTAY = 'farmstay';
    case GUEST_HOUSE = 'guest_house';
    case HOSTEL = 'hostel';
    case HOTEL = 'hotel';
    case LODGING = 'lodging';
    case MOTEL = 'motel';
    case PRIVATE_GUEST_ROOM = 'private_guest_room';
    case RESORT_HOTEL = 'resort_hotel';
    case RV_PARK = 'rv_park';

    // Places of Worship
    case CHURCH = 'church';
    case HINDU_TEMPLE = 'hindu_temple';
    case MOSQUE = 'mosque';
    case SYNAGOGUE = 'synagogue';

    // Shopping
    case AUTO_PARTS_STORE = 'auto_parts_store';
    case BICYCLE_STORE = 'bicycle_store';
    case BOOK_STORE = 'book_store';
    case CELL_PHONE_STORE = 'cell_phone_store';
    case CLOTHING_STORE = 'clothing_store';
    case CONVENIENCE_STORE = 'convenience_store';
    case DEPARTMENT_STORE = 'department_store';
    case DISCOUNT_STORE = 'discount_store';
    case ELECTRONICS_STORE = 'electronics_store';
    case FURNITURE_STORE = 'furniture_store';
    case GIFT_SHOP = 'gift_shop';
    case GROCERY_STORE = 'grocery_store';
    case HARDWARE_STORE = 'hardware_store';
    case HOME_GOODS_STORE = 'home_goods_store';
    case HOME_IMPROVEMENT_STORE = 'home_improvement_store';
    case JEWELRY_STORE = 'jewelry_store';
    case LIQUOR_STORE = 'liquor_store';
    case MARKET = 'market';
    case PET_STORE = 'pet_store';
    case SHOE_STORE = 'shoe_store';
    case SHOPPING_MALL = 'shopping_mall';
    case SPORTING_GOODS_STORE = 'sporting_goods_store';
    case STORE = 'store';
    case SUPERMARKET = 'supermarket';
    case WHOLESALER = 'wholesaler';

    // Sports
    case ATHLETIC_FIELD = 'athletic_field';
    case FITNESS_CENTER = 'fitness_center';
    case GOLF_COURSE = 'golf_course';
    case GYM = 'gym';
    case PLAYGROUND = 'playground';
    case SKI_RESORT = 'ski_resort';
    case SPORTS_CLUB = 'sports_club';
    case SPORTS_COMPLEX = 'sports_complex';
    case STADIUM = 'stadium';
    case SWIMMING_POOL = 'swimming_pool';

    // Transportation
    case AIRPORT = 'airport';
    case BUS_STATION = 'bus_station';
    case BUS_STOP = 'bus_stop';
    case FERRY_TERMINAL = 'ferry_terminal';
    case HELIPORT = 'heliport';
    case LIGHT_RAIL_STATION = 'light_rail_station';
    case PARK_AND_RIDE = 'park_and_ride';
    case SUBWAY_STATION = 'subway_station';
    case TAXI_STAND = 'taxi_stand';
    case TRAIN_STATION = 'train_station';
    case TRANSIT_DEPOT = 'transit_depot';
    case TRANSIT_STATION = 'transit_station';
    case TRUCK_STOP = 'truck_stop';

    public function toTranslatable(): TranslatableMessage
    {
        return t('place_type.' . $this->value);
    }
}
