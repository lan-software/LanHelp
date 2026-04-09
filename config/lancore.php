<?php

/*
|--------------------------------------------------------------------------
| LanHelp-specific LanCore overrides
|--------------------------------------------------------------------------
|
| The base config is published by the lan-software/lancore-client package.
| This file adds keys specific to LanHelp (merged on top of the package
| config via config:publish or manual placement).
|
*/

return [
    'app_slug' => env('LANCORE_APP_SLUG', 'lanhelp'),

    /*
    |--------------------------------------------------------------------------
    | Announcements Feed
    |--------------------------------------------------------------------------
    |
    | URL of the public LanCore announcements feed consumed by LanHelp.
    | Falls back to LANCORE_BASE_URL + /api/announcements/feed.
    |
    */
    'announcements_feed_url' => env(
        'LANCORE_ANNOUNCEMENTS_FEED_URL',
        rtrim(env('LANCORE_INTERNAL_URL') ?? env('LANCORE_BASE_URL', 'http://lancore.lan'), '/').'/api/announcements/feed'
    ),
];
