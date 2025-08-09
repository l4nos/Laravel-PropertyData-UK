<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataApiException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataAuthenticationException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataConnectionException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataRateLimitException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataServerException;
use Lanos\LaravelPropertyData\Http\PropertyDataClient;
use Lanos\LaravelPropertyData\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class PropertyDataClientTest extends TestCase
{
    private PropertyDataClient $client;

    private Client&MockInterface $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(Client::class);
        $this->client = new PropertyDataClient([
            'base_url' => 'https://api.test.propertydata.co.uk',
            'key' => 'test-api-key',
            'timeout' => 30,
            'logging_enabled' => false,
        ]);

        // Use reflection to inject the mock HTTP client
        $reflection = new \ReflectionClass($this->client);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->client, $this->httpClient);
    }

    /** @test */
    public function it_makes_successful_get_request(): void
    {
        $responseData = ['data' => 'test'];
        $response = new Response(200, [], json_encode($responseData));

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with('https://api.test.propertydata.co.uk/test-endpoint', [
                'query' => ['api_key' => 'test-api-key', 'postcode' => 'SW1A 1AA'],
            ])
            ->andReturn($response);

        $result = $this->client->get('test-endpoint', ['postcode' => 'SW1A 1AA']);

        $this->assertEquals($responseData, $result);
    }

    /** @test */
    public function it_throws_authentication_exception_on_401(): void
    {
        $request = new Request('GET', 'https://api.test.propertydata.co.uk/test-endpoint');
        $response = new Response(401, [], 'Unauthorized');
        $exception = new ClientException('Unauthorized', $request, $response);

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andThrow($exception);

        $this->expectException(PropertyDataAuthenticationException::class);
        $this->expectExceptionMessage('Invalid Property Data API key or authentication failed');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_throws_rate_limit_exception_on_429(): void
    {
        $request = new Request('GET', 'https://api.test.propertydata.co.uk/test-endpoint');
        $response = new Response(429, [], 'Too Many Requests');
        $exception = new ClientException('Too Many Requests', $request, $response);

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andThrow($exception);

        $this->expectException(PropertyDataRateLimitException::class);
        $this->expectExceptionMessage('Property Data API rate limit exceeded');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_throws_server_exception_on_500(): void
    {
        $request = new Request('GET', 'https://api.test.propertydata.co.uk/test-endpoint');
        $response = new Response(500, [], 'Internal Server Error');
        $exception = new ServerException('Internal Server Error', $request, $response);

        $this->httpClient
            ->shouldReceive('get')
            ->times(3) // Original attempt + 2 retries
            ->andThrow($exception);

        $this->expectException(PropertyDataServerException::class);
        $this->expectExceptionMessage('Property Data API server error');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_throws_connection_exception_on_network_error(): void
    {
        $request = new Request('GET', 'https://api.test.propertydata.co.uk/test-endpoint');
        $exception = new ConnectException('Connection refused', $request);

        $this->httpClient
            ->shouldReceive('get')
            ->times(3) // Original attempt + 2 retries
            ->andThrow($exception);

        $this->expectException(PropertyDataConnectionException::class);
        $this->expectExceptionMessage('Unable to connect to Property Data API');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_throws_exception_for_invalid_json_response(): void
    {
        $response = new Response(200, [], 'invalid json{');

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andReturn($response);

        $this->expectException(PropertyDataApiException::class);
        $this->expectExceptionMessage('Invalid JSON response from Property Data API');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_throws_exception_for_empty_response(): void
    {
        $response = new Response(200, [], '');

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andReturn($response);

        $this->expectException(PropertyDataApiException::class);
        $this->expectExceptionMessage('Empty response from Property Data API');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_throws_exception_for_non_array_json_response(): void
    {
        $response = new Response(200, [], '"string response"');

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andReturn($response);

        $this->expectException(PropertyDataApiException::class);
        $this->expectExceptionMessage('Property Data API response is not a valid array');

        $this->client->get('test-endpoint');
    }

    /** @test */
    public function it_validates_configuration_on_construction(): void
    {
        $this->expectException(PropertyDataApiException::class);
        $this->expectExceptionMessage('Property Data API key is not configured');

        new PropertyDataClient(['base_url' => 'https://api.test.com', 'key' => '']);
    }

    /** @test */
    public function it_validates_base_url_format(): void
    {
        $this->expectException(PropertyDataApiException::class);
        $this->expectExceptionMessage('Invalid Property Data API base URL format');

        new PropertyDataClient(['base_url' => 'invalid-url', 'key' => 'test-key']);
    }

    /** @test */
    public function it_gets_account_credits(): void
    {
        $responseData = ['credits_remaining' => 1000, 'monthly_limit' => 5000];
        $response = new Response(200, [], json_encode($responseData));

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with('https://api.test.propertydata.co.uk/account/credits', [
                'query' => ['api_key' => 'test-api-key'],
            ])
            ->andReturn($response);

        $result = $this->client->getAccountCredits();

        $this->assertEquals($responseData, $result);
    }

    /** @test */
    public function test_connection_returns_true_on_success(): void
    {
        $responseData = ['credits_remaining' => 1000];
        $response = new Response(200, [], json_encode($responseData));

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andReturn($response);

        $this->assertTrue($this->client->testConnection());
    }

    /** @test */
    public function test_connection_returns_false_on_auth_failure(): void
    {
        $request = new Request('GET', 'https://api.test.propertydata.co.uk/account/credits');
        $response = new Response(401, [], 'Unauthorized');
        $exception = new ClientException('Unauthorized', $request, $response);

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->andThrow($exception);

        $this->assertFalse($this->client->testConnection());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
