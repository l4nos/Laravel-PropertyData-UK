<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Tests\Feature;

use Lanos\LaravelPropertyData\Facades\PropertyData;
use Lanos\LaravelPropertyData\Tests\TestCase;

class LaravelPropertyDataTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertNotNull(app('laravel-property-data'));
    }

    /** @test */
    public function it_has_facade_access(): void
    {
        $this->assertInstanceOf(
            \Lanos\LaravelPropertyData\LaravelPropertyData::class,
            PropertyData::getFacadeRoot()
        );
    }

    /** @test */
    public function it_loads_configuration(): void
    {
        $this->assertEquals('test-api-key', config('property-data.api.key'));
        $this->assertEquals('https://api.test.propertydata.co.uk', config('property-data.api.base_url'));
        $this->assertEquals(10, config('property-data.api.timeout'));
    }

    /** @test */
    public function it_returns_version(): void
    {
        $instance = app('laravel-property-data');
        $this->assertEquals('1.0.0', $instance->version());
    }
}
