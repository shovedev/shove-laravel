<?php

namespace Shove\Laravel\Providers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Shove\Laravel\Queue\ShoveConnector;
use Shove\Connector\ShoveConnector as ShoveHttpClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;

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

        $this->app->singleton(ShoveHttpClient::class, function ($app) {
            return new ShoveHttpClient(Config::get('shove.secret'), Config::get('shove.base_url'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::extend('shove', fn() => new ShoveConnector($this->app['request']));

        $this->app->booted(function () {
            Config::set([
                'queue.connections.shove' => [
                    'driver' => App::environment('testing') ? 'sync' : 'shove',
                    'queue' => 'default',
                ],
            ]);
        });

        $this->publishes([
            __DIR__.'/../../config/shove.php' => config_path('shove.php'),
        ], 'shove-config');
    }

    public function provides(): array
    {
        return [
            ShoveHttpClient::class,
        ];
    }
}