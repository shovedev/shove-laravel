<?php

namespace Shove\Laravel;

use Illuminate\Support\Facades\Route;
use Shove\Laravel\Http\Controllers\WebhookController;

class Shove
{
    public static function routes($endpoint): void
    {
        Route::post($endpoint, WebhookController::class);
    }
}