<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Tests\Unit;

use Lanos\LaravelPropertyData\Http\PropertyDataClient;
use Lanos\LaravelPropertyData\LaravelPropertyData;
use Lanos\LaravelPropertyData\LaravelPropertyDataServiceProvider;
use Lanos\LaravelPropertyData\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_property_data_client_as_singleton(): void
    {
        $client1 = $this->app->make(PropertyDataClient::class);
        $client2 = $this->app->make(PropertyDataClient::class);

        $this->assertInstanceOf(PropertyDataClient::class, $client1);
        $this->assertSame($client1, $client2);
    }

    /** @test */
    public function it_registers_main_service_as_singleton(): void
    {
        $service1 = $this->app->make('laravel-property-data');
        $service2 = $this->app->make('laravel-property-data');

        $this->assertInstanceOf(LaravelPropertyData::class, $service1);
        $this->assertSame($service1, $service2);
    }

    /** @test */
    public function it_registers_alias_for_main_service(): void
    {
        $service1 = $this->app->make('laravel-property-data');
        $service2 = $this->app->make(LaravelPropertyData::class);

        $this->assertSame($service1, $service2);
    }

    /** @test */
    public function it_provides_correct_services(): void
    {
        $provider = new LaravelPropertyDataServiceProvider($this->app);
        
        $provides = $provider->provides();
        
        $this->assertContains('laravel-property-data', $provides);
    }

    /** @test */
    public function it_merges_configuration(): void
    {
        // Configuration should be merged from the package
        $this->assertTrue(config()->has('property-data.api.base_url'));
        $this->assertTrue(config()->has('property-data.api.key'));
        $this->assertTrue(config()->has('property-data.api.timeout'));
        $this->assertTrue(config()->has('property-data.logging.enabled'));
        $this->assertTrue(config()->has('property-data.logging.channel'));
    }

    /** @test */
    public function it_injects_client_into_main_service(): void
    {
        $service = $this->app->make('laravel-property-data');
        $client = $this->app->make(PropertyDataClient::class);

        // Use reflection to verify the client was injected
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $injectedClient = $property->getValue($service);

        $this->assertSame($client, $injectedClient);
    }

    /** @test */
    public function service_provider_uses_configuration_for_client(): void
    {
        // Create a new client directly with custom configuration
        $customConfig = [
            'base_url' => 'https://test.api.com',
            'key' => 'test-key-123',
            'timeout' => 45,
            'logging_enabled' => false,
            'log_channel' => 'custom',
        ];

        $client = new PropertyDataClient($customConfig);

        // Use reflection to check the client was configured correctly
        $reflection = new \ReflectionClass($client);
        
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $this->assertEquals('https://test.api.com', $baseUrlProperty->getValue($client));

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $this->assertEquals('test-key-123', $apiKeyProperty->getValue($client));

        $timeoutProperty = $reflection->getProperty('timeout');
        $timeoutProperty->setAccessible(true);
        $this->assertEquals(45, $timeoutProperty->getValue($client));

        $loggingProperty = $reflection->getProperty('loggingEnabled');
        $loggingProperty->setAccessible(true);
        $this->assertFalse($loggingProperty->getValue($client));

        $logChannelProperty = $reflection->getProperty('logChannel');
        $logChannelProperty->setAccessible(true);
        $this->assertEquals('custom', $logChannelProperty->getValue($client));
    }
}