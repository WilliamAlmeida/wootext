<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Ensure CORS headers are returned for auth routes used by Fortify
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'two-factor/*', 'livewire/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://chat.wsl.local', 'https://chat.wsl.local'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
