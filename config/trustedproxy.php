<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Comma-separated IPs or CIDR ranges of the proxies in front of the app, or
    | "*" to trust whichever host connects.  When empty, the framework trusts
    | all proxies on Laravel Cloud, Forge, and Vapor, and none elsewhere.
    |
    */

    'proxies' => env('TRUSTED_PROXIES') ?: null,

    /*
    |--------------------------------------------------------------------------
    | Client IP Header
    |--------------------------------------------------------------------------
    |
    | Header, set by the outermost trusted proxy, holding only the visitor's
    | IP.  It takes precedence over X-Forwarded-For, whose leading entries are
    | supplied by the client.  When empty, Laravel Cloud uses CF-Connecting-IP
    | and other hosts use X-Forwarded-For.  See App\Http\Middleware\TrustProxies.
    |
    */

    'client_ip_header' => env('TRUSTED_PROXY_CLIENT_IP_HEADER') ?: null,

];
