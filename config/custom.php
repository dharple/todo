<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Sent on web responses.  With report_only enabled, the browser logs
    | violations to the console instead of blocking them; use that locally,
    | where Laravel's debug error pages rely on inline scripts.
    |
    | Bootstrap's CSS uses data: SVGs for some icons, so img-src allows data:.
    |
    */

    'csp' => [
        'report_only' => (bool) env('CSP_REPORT_ONLY', false),
        'directives' => [
            'default-src'     => ["'self'"],
            'script-src'      => ["'self'", 'https://cdnjs.cloudflare.com'],
            'style-src'       => ["'self'", 'https://cdnjs.cloudflare.com'],
            'font-src'        => ["'self'", 'https://cdnjs.cloudflare.com'],
            'img-src'         => ["'self'", 'data:'],
            'connect-src'     => ["'self'"],
            'object-src'      => ["'none'"],
            'base-uri'        => ["'self'"],
            'form-action'     => ["'self'"],
            'frame-ancestors' => ["'none'"],
        ],
    ],

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
