<?php

namespace App\Http\Controllers\Utils;

enum GoogleEnum: string
{
    case ADD_TO_CART = 'add_to_cart';
    case INITIATE_CHECKOUT = 'begin_checkout';
    case PURCHASE = 'purchase_done';
}
