<?php

return [

    /*
    |--------------------------------------------------------------------------
    | One-time unlock purchase
    |--------------------------------------------------------------------------
    |
    | The unlimited version is a single Stripe price. Users are sent to a
    | Checkout Session created for them (with their id as client_reference_id
    | and a "back to Abrio" arrow); the checkout.session.completed webhook
    | (handled through Cashier) then unlocks the account.
    |
    */

    'unlock' => [
        'price_id' => env('STRIPE_UNLOCK_PRICE_ID'),
    ],

];
