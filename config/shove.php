<?php

return [
    'secret' => env('SHOVE_API_TOKEN', ''),

    'signing_secret' => env('SHOVE_SIGNING_SECRET', ''),

    'default_queue' => 'default'
];