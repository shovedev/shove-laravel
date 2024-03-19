<?php

return [
    'secret' => env('SHOVE_API_TOKEN', ''),

    'signing_secret' => env('SHOVE_SIGNING_SECRET', ''),

    'default_queue' => env('SHOVE_DEFAULT_QUEUE', 'default'),

    'api_url' => env('SHOVE_API_URL', 'https://shove.dev/api'),
];