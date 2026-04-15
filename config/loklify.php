<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Loklify API URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Loklify instance.
    |
    */
    'url' => env('LOKLIFY_URL', 'https://api.loklify.com'),

    /*
    |--------------------------------------------------------------------------
    | Project ID
    |--------------------------------------------------------------------------
    |
    | The UUID of the Loklify project to fetch translations from.
    |
    */
    'project_id' => env('LOKLIFY_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | The Bearer token used to authenticate requests to the Loklify API.
    |
    */
    'token' => env('LOKLIFY_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) translations are cached locally.
    | Set to 0 to disable caching.
    |
    */
    'cache_ttl' => env('LOKLIFY_CACHE_TTL', 3600),
];
