<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData;

use Illuminate\Support\ServiceProvider;

class LaravelPropertyDataServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/property-data.php',
            'property-data'
        );

        // Register the HTTP client
        $this->app->singleton(\Lanos\LaravelPropertyData\Http\PropertyDataClient::class, function ($app) {
            return new \Lanos\LaravelPropertyData\Http\PropertyDataClient([
                'base_url' => config('property-data.api.base_url'),
                'key' => config('property-data.api.key'),
                'timeout' => config('property-data.api.timeout'),
                'logging_enabled' => config('property-data.logging.enabled'),
                'log_channel' => config('property-data.logging.channel'),
            ]);
        });

        // Register the main class binding
        $this->app->singleton('laravel-property-data', function ($app) {
            return new LaravelPropertyData(
                $app->make(\Lanos\LaravelPropertyData\Http\PropertyDataClient::class)
            );
        });

        // Alias for easier access
        $this->app->alias('laravel-property-data', LaravelPropertyData::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publishing configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/property-data.php' => config_path('property-data.php'),
            ], 'property-data-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            'laravel-property-data',
        ];
    }
}
