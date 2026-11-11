<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Throttling
    |--------------------------------------------------------------------------
    |
    | See \Illuminate\Cache\RateLimiting\Limit for details.
    |
    */

    'throttling' => [
        'login' => [
            'ip' => [
                'maxAttempts' => 3,
                'decaySeconds' => 60,
            ],
            'username' => [
                'maxAttempts' => 5,
                'decaySeconds' => 60,
            ],
        ],
    ],

];
