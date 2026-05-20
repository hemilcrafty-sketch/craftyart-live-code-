<?php

return [

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | We use ONLY the `api` guard because the frontend handles login.
    | This guard uses token-based stateless auth (no sessions).
    |
    */

    'guards' => [
        'api' => [
            'driver' => 'token',
            'provider' => 'userdata', // ✅ use user_data table
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | This tells Laravel that our users are stored in `user_data` table
    | using the UserData model.
    |
    */

    'providers' => [
        'userdata' => [
            'driver' => 'eloquent',
            'model' => App\Models\UserData::class, // ✅ your model
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password reset (not needed, safe to leave default)
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'userdata',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
