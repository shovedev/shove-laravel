<?php

namespace Shove\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Shove\Connector\ShoveConnector;
use Shove\Laravel\Http\Controllers\WebhookController;

class Shove extends Facade
{
    public static function routes(string $endpoint = '/shove'): void
    {
        Route::post($endpoint, WebhookController::class);

        if (class_exists('Illuminate\Foundation\Http\Middleware\ValidateCsrfToken')) {
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::except([
                $endpoint
            ]);
        }
    }

    protected static function getFacadeAccessor(): string
    {
        return ShoveConnector::class;
    }
}