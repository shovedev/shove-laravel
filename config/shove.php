<?php

return [
    'secret' => env('SHOVE_API_TOKEN', ''),
    'api_url' => env('SHOVE_API_URL', ''),
    'signing_secret' => env('SHOVE_SIGNING_SECRET', ''),
    'default_queue' => 'default'
];