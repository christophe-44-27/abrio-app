<?php

return [

    /*
    |--------------------------------------------------------------------------
    | One-time unlock purchase
    |--------------------------------------------------------------------------
    |
    | The unlimited version is a single Stripe Payment Link. Users are sent to
    | "link" with their id as client_reference_id; the checkout.session.completed
    | webhook (handled through Cashier) then unlocks the account.
    |
    | "payment_link_id" (plink_...) is optional but recommended: when set, only
    | payments made through that link unlock an account, so other products sold
    | from the same Stripe account can never do it.
    |
    */

    'unlock' => [
        'link' => env('STRIPE_UNLOCK_LINK'),
        'payment_link_id' => env('STRIPE_UNLOCK_PAYMENT_LINK_ID'),
    ],

];
