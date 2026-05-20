<?php

namespace App\Http\Controllers\Utils;

enum FacebookEvent: string
{
    case ADD_TO_PAYMENT = 'Add To Payment';
    case ADD_TO_CART = 'Add To Cart';
    case COMPLETE_REGISTRATION = 'Complete Registration';
    case INITIATE_CHECKOUT = 'Initiate Checkout';
    case PURCHASE = 'Purchase';
    case SUBSCRIBE = 'Subscribe';
    case SELLING = 'Selling';
    case VIEW_CONTENT = 'View Content';
    case EXPORT = 'Export';
    case DOWNLOAD = 'Download';
}
