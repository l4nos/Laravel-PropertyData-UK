<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Tests\Unit;

use Lanos\LaravelPropertyData\Http\PropertyDataClient;
use Lanos\LaravelPropertyData\LaravelPropertyData;
use Lanos\LaravelPropertyData\Tests\TestCase;
use Lanos\LaravelPropertyData\Enums\ProjectType;
use Lanos\LaravelPropertyData\Enums\FinishQuality;
use Lanos\LaravelPropertyData\Enums\DecisionRating;
use Mockery;
use Mockery\MockInterface;

class LaravelPropertyDataTest extends TestCase
{
    private LaravelPropertyData $propertyData;
    private PropertyDataClient&MockInterface $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = Mockery::mock(PropertyDataClient::class);
        $this->propertyData = new LaravelPropertyData($this->client);
    }

    /** @test */
    public function it_returns_version(): void
    {
        $this->assertEquals('1.0.0', $this->propertyData->version());
    }

    /** @test */
    public function it_delegates_test_connection_to_client(): void
    {
        $this->client
            ->shouldReceive('testConnection')
            ->once()
            ->andReturn(true);

        $result = $this->propertyData->testConnection();

        $this->assertTrue($result);
    }

    /** @test */
    public function it_makes_address_match_uprn_request(): void
    {
        $expectedResponse = [
            ['uprn' => '123456', 'address' => '123 Test Street']
        ];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('address-match-uprn', ['address' => '123 Test Street, London'])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->addressMatchUprn('123 Test Street, London');

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_uprn_lookup_request(): void
    {
        $expectedResponse = ['uprn' => '123456', 'address' => '123 Test Street'];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('uprn', ['uprn' => '123456'])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->uprn('123456');

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_multiple_uprns_lookup_request(): void
    {
        $expectedResponse = [
            ['uprn' => '123456', 'address' => '123 Test Street'],
            ['uprn' => '789012', 'address' => '456 Test Avenue']
        ];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('uprns', ['uprns' => '123456,789012'])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->uprns(['123456', '789012']);

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_prices_request_with_pagination(): void
    {
        $expectedResponse = ['data' => [], 'pagination' => []];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('prices', [
                'postcode' => 'SW1A 1AA',
                'page' => 2,
                'per_page' => 50
            ])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->prices('SW1A 1AA', 2, 50);

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_demand_request_with_optional_parameters(): void
    {
        $expectedResponse = ['demand_rating' => 'Seller\'s market'];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('demand', [
                'postcode' => 'SW1A 1AA',
                'location' => '51.501,-0.141'
            ])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->demand(
            postcode: 'SW1A 1AA',
            location: '51.501,-0.141'
        );

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_filters_null_parameters(): void
    {
        $expectedResponse = ['demand_rating' => 'Balanced market'];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('demand', ['town' => 'London'])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->demand(
            postcode: null,
            location: null,
            w3w: null,
            town: 'London'
        );

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_valuation_sale_request(): void
    {
        $expectedResponse = ['estimated_value' => 450000];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('valuation-sale', [
                'postcode' => 'SW1A 1AA',
                'beds' => 3,
                'sqft' => 1200.5
            ])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->valuationSale('SW1A 1AA', 3, 1200.5);

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_development_calculator_request(): void
    {
        $expectedResponse = ['profit' => 150000, 'margin' => 25];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('development-calculator', [
                'postcode' => 'SW1A 1AA',
                'purchase_price' => 500000,
                'sqft_pre_development' => 1000,
                'sqft_post_development' => 1500,
                'project_type' => 'refurbish',
                'finish_quality' => 'premium'
            ])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->developmentCalculator(
            'SW1A 1AA',
            500000,
            1000,
            1500,
            ProjectType::REFURBISH,
            FinishQuality::PREMIUM
        );

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_planning_applications_request_with_filters(): void
    {
        $expectedResponse = ['applications' => []];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('planning-applications', [
                'postcode' => 'SW1A 1AA',
                'decision_rating' => 'positive',
                'category' => 'residential,commercial',
                'max_age' => 365
            ])
            ->andReturn($expectedResponse);

        $result = $this->propertyData->planningApplications(
            postcode: 'SW1A 1AA',
            decisionRating: DecisionRating::POSITIVE,
            category: 'residential,commercial',
            maxAge: 365
        );

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_makes_account_credits_request(): void
    {
        $expectedResponse = ['credits_remaining' => 1000];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('account/credits')
            ->andReturn($expectedResponse);

        $result = $this->propertyData->accountCredits();

        $this->assertEquals($expectedResponse, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}