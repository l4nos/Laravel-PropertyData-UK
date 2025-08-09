<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Tests\Unit;

use Lanos\LaravelPropertyData\Exceptions\PropertyDataApiException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataAuthenticationException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataConnectionException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataRateLimitException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataServerException;
use Lanos\LaravelPropertyData\Tests\TestCase;

class ExceptionsTest extends TestCase
{
    /** @test */
    public function property_data_api_exception_extends_exception(): void
    {
        $exception = new PropertyDataApiException('Test message', 400);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    /** @test */
    public function property_data_api_exception_has_context_method(): void
    {
        $exception = new PropertyDataApiException('Test message', 400);
        $context = $exception->getContext();

        $this->assertIsArray($context);
        $this->assertArrayHasKey('message', $context);
        $this->assertArrayHasKey('code', $context);
        $this->assertArrayHasKey('file', $context);
        $this->assertArrayHasKey('line', $context);
        $this->assertEquals('Test message', $context['message']);
        $this->assertEquals(400, $context['code']);
    }

    /** @test */
    public function authentication_exception_extends_api_exception(): void
    {
        $exception = new PropertyDataAuthenticationException('Auth failed', 401);

        $this->assertInstanceOf(PropertyDataApiException::class, $exception);
        $this->assertEquals('Auth failed', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    /** @test */
    public function connection_exception_extends_api_exception(): void
    {
        $exception = new PropertyDataConnectionException('Connection failed', 0);

        $this->assertInstanceOf(PropertyDataApiException::class, $exception);
        $this->assertEquals('Connection failed', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function rate_limit_exception_extends_api_exception(): void
    {
        $exception = new PropertyDataRateLimitException('Rate limited', 429);

        $this->assertInstanceOf(PropertyDataApiException::class, $exception);
        $this->assertEquals('Rate limited', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
    }

    /** @test */
    public function server_exception_extends_api_exception(): void
    {
        $exception = new PropertyDataServerException('Server error', 500);

        $this->assertInstanceOf(PropertyDataApiException::class, $exception);
        $this->assertEquals('Server error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    /** @test */
    public function exceptions_accept_previous_exception(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new PropertyDataApiException('Test message', 400, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
