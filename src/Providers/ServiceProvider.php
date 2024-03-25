<?php

namespace Shove\Laravel\Providers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Shove\Connector\ShoveConnector as Shove;
use Shove\Laravel\Queue\ShoveConnector;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/shove.php', 'shove'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::extend('shove', fn() => new ShoveConnector($this->app['request']));

        $this->app->singleton(Shove::class, function () {
            return new Shove(
                token: config('shove.secret'),
                baseUrl: config('shove.api_url')
            );
        });

        $this->app->booted(function () {
            config([
                'queue.connections.shove' => [
                    'driver' => 'shove',
                    'queue' => 'default',
                ],
            ]);
        });

        $this->publishes([
            __DIR__.'/../../config/shove.php' => config_path('shove.php'),
        ]);
    }
}