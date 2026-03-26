<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LanCore Integration
    |--------------------------------------------------------------------------
    |
    | Toggle the LanCore SSO integration on or off. When disabled, the local
    | Fortify login form is shown instead of redirecting to LanCore.
    |
    */
    'enabled' => env('LANCORE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | LanCore URLs
    |--------------------------------------------------------------------------
    |
    | base_url  — Browser-facing URL used for SSO authorize redirects.
    | internal_url — Server-to-server URL used for API calls (Docker fix).
    |                Falls back to base_url if not set.
    |
    */
    'base_url' => env('LANCORE_BASE_URL', 'http://lancore.lan'),

    'internal_url' => env('LANCORE_INTERNAL_URL') ?? env('LANCORE_BASE_URL', 'http://lancore.lan'),

    /*
    |--------------------------------------------------------------------------
    | Integration Credentials
    |--------------------------------------------------------------------------
    */
    'token' => env('LANCORE_TOKEN'),

    'app_slug' => env('LANCORE_APP_SLUG', 'lanhelp'),

    'callback_url' => env('LANCORE_CALLBACK_URL', env('APP_URL').'/auth/lancore/callback'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Tuning
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('LANCORE_TIMEOUT', 5),

    'retries' => (int) env('LANCORE_RETRIES', 2),

    'retry_delay' => (int) env('LANCORE_RETRY_DELAY', 100),
];
