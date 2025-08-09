<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Tests;

use Lanos\LaravelPropertyData\LaravelPropertyDataServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelPropertyDataServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        // Set up configuration for testing
        config()->set('property-data.api.key', 'test-api-key');
        config()->set('property-data.api.base_url', 'https://api.test.propertydata.co.uk');
        config()->set('property-data.api.timeout', 10);
        config()->set('property-data.logging.enabled', false);
    }
}
