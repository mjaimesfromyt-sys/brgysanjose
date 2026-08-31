<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Barangay identity
    |--------------------------------------------------------------------------
    |
    | Shown in the header and footer of every outgoing email (see
    | resources/views/components/mail-shell.blade.php). Anything left blank is
    | simply omitted from the footer rather than rendered empty.
    |
    */

    'name'         => 'Barangay San Jose',
    'municipality' => 'Talibon, Bohol',

    'address' => env('BARANGAY_ADDRESS', 'Purok 5, San Jose, Talibon, Bohol'),
    'email'   => env('BARANGAY_EMAIL', 'blgusanjosetalibon1910@gmail.com'),
    'phone'   => env('BARANGAY_PHONE'),

    'facebook' => [
        'name' => env('BARANGAY_FACEBOOK_NAME', 'Barangay San Jose'),
        'url'  => env('BARANGAY_FACEBOOK_URL'),
    ],

];
