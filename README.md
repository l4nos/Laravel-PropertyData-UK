# Laravel Property Data

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lanos/laravel-property-data.svg?style=flat-square)](https://packagist.org/packages/lanos/laravel-property-data)
[![Total Downloads](https://img.shields.io/packagist/dt/lanos/laravel-property-data.svg?style=flat-square)](https://packagist.org/packages/lanos/laravel-property-data)
[![PHP Version](https://img.shields.io/packagist/php-v/lanos/laravel-property-data.svg?style=flat-square)](https://packagist.org/packages/lanos/laravel-property-data)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-orange.svg?style=flat-square)](https://laravel.com)

A comprehensive Laravel package for integrating with the PropertyData UK property data API. This package provides a clean, intuitive interface for accessing UK property market data, valuations, planning information, and area analytics.

## 📋 Table of Contents

- [About PropertyData API](#about-propertydata-api)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Available Methods](#available-methods)
- [Usage Examples](#usage-examples)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)
- [Disclaimer](#disclaimer)

## About PropertyData API

This package integrates with the **PropertyData API** - a comprehensive UK property data service. 

⚠️ **Important**: You need an API key to use this package.

👉 **Get your API key at: [https://propertydata.co.uk](https://propertydata.co.uk)**

📚 **API Documentation: [https://propertydata.co.uk/api/documentation](https://propertydata.co.uk/api/documentation)**

PropertyData provides access to:
- Property valuations and market analytics
- Planning applications and building regulations
- Area demographics and statistics
- Crime data and school information
- Development feasibility calculations
- And much more...

## Features

✨ **Key Features:**

- 🏠 **Comprehensive Property Data**: Access sold prices, valuations, rental yields, and market trends
- 📍 **Location Intelligence**: Area demographics, crime statistics, school ratings, and amenities
- 🏗️ **Development Tools**: Development calculators, GDV estimates, build costs, and feasibility analysis
- 📋 **Planning Data**: Planning applications, conservation areas, green belt checks, and listed buildings
- 🔍 **Property Search**: UPRN lookups, address matching, and title searches
- 💰 **Financial Calculators**: Mortgage calculators, stamp duty, and rebuild cost estimates
- 🔄 **Automatic Retry Logic**: Built-in retry mechanism for transient failures
- ✅ **Input Validation**: Comprehensive validation for all API parameters
- 🎯 **Type Safety**: PHP 8.1+ enums for constrained values
- 📝 **Full Laravel Integration**: Service provider, facade, and configuration publishing

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Composer
- PropertyData API key (get one at [propertydata.co.uk](https://propertydata.co.uk))

## Installation

### Step 1: Install via Composer

```bash
composer require lanos/laravel-property-data
```

### Step 2: Publish Configuration (Optional)

The package will auto-register its service provider through Laravel's package discovery. To publish the configuration file:

```bash
php artisan vendor:publish --tag=property-data-config
```

This will create a `config/property-data.php` configuration file.

### Step 3: Add API Key to Environment

Add your PropertyData API key to your `.env` file:

```env
PROPERTY_DATA_API_KEY=your-api-key-here
```

You can also configure optional settings:

```env
# API Configuration
PROPERTY_DATA_API_URL=https://api.propertydata.co.uk
PROPERTY_DATA_API_TIMEOUT=30

# Logging
PROPERTY_DATA_LOGGING_ENABLED=true
PROPERTY_DATA_LOG_CHANNEL=stack

# Retry Configuration
PROPERTY_DATA_RETRY_ENABLED=true
PROPERTY_DATA_RETRY_ATTEMPTS=3
PROPERTY_DATA_RETRY_DELAY=1000
```

## Configuration

The configuration file (`config/property-data.php`) contains:

```php
return [
    'api' => [
        'base_url' => env('PROPERTY_DATA_API_URL', 'https://api.propertydata.co.uk'),
        'key' => env('PROPERTY_DATA_API_KEY', ''),
        'timeout' => env('PROPERTY_DATA_API_TIMEOUT', 30),
        
        'retry' => [
            'enabled' => env('PROPERTY_DATA_RETRY_ENABLED', true),
            'max_attempts' => env('PROPERTY_DATA_RETRY_ATTEMPTS', 3),
            'delay' => env('PROPERTY_DATA_RETRY_DELAY', 1000), // milliseconds
        ],
    ],
    
    'logging' => [
        'enabled' => env('PROPERTY_DATA_LOGGING_ENABLED', true),
        'channel' => env('PROPERTY_DATA_LOG_CHANNEL', config('logging.default')),
    ],
];
```

## Quick Start

### Using the Facade

```php
use PropertyData;

// Get property valuation
$valuation = PropertyData::valuationSale('SW1A 1AA', beds: 3, sqft: 1200);

// Get area demographics
$demographics = PropertyData::demographics(postcode: 'SW1A 1AA');

// Check planning applications
$planning = PropertyData::planningApplications(postcode: 'SW1A 1AA');
```

### Using Dependency Injection

```php
use Lanos\LaravelPropertyData\LaravelPropertyData;

class PropertyController extends Controller
{
    public function __construct(
        private LaravelPropertyData $propertyData
    ) {}
    
    public function show(string $postcode)
    {
        $data = $this->propertyData->prices($postcode);
        
        return view('property.show', compact('data'));
    }
}
```

## Available Methods

### 📍 Address & UPRN Methods

```php
// Match address to UPRN
$uprns = PropertyData::addressMatchUprn('123 Example Street, London, SW1A 1AA');

// Lookup property by UPRN
$property = PropertyData::uprn('123456789012');

// Lookup multiple UPRNs
$properties = PropertyData::uprns(['123456789012', '123456789013']);

// Get Land Registry title for UPRN
$title = PropertyData::uprnTitle('123456789012');
```

### 💰 Market Data Methods

```php
// Current asking prices
$prices = PropertyData::prices('SW1A 1AA', page: 1, perPage: 20);

// Prices per square foot
$pricesPerSqf = PropertyData::pricesPerSqf('SW1A 1AA');

// Sold prices from Land Registry
$soldPrices = PropertyData::soldPrices('SW1A 1AA');

// Rental market statistics
$rents = PropertyData::rents('SW1A 1AA');

// HMO rent estimates
$hmoRents = PropertyData::rentsHmo('SW1A 1AA');

// Yield calculations
$yields = PropertyData::yields('SW1A 1AA');

// Price growth metrics
$growth = PropertyData::growth('SW1A 1AA');

// Property demand analytics
$demand = PropertyData::demand(postcode: 'SW1A 1AA');
```

### 🏠 Valuation Methods

```php
// Instant sale valuation
$valuation = PropertyData::valuationSale('SW1A 1AA', beds: 3, sqft: 1200);

// Rent valuation
$rentValuation = PropertyData::valuationRent('SW1A 1AA');

// HMO valuation
$hmoValuation = PropertyData::valuationHmo('SW1A 1AA');

// Historical valuations
$history = PropertyData::valuationHistorical('SW1A 1AA', page: 1);
```

### 🏗️ Development Methods

```php
use Lanos\LaravelPropertyData\Enums\ProjectType;
use Lanos\LaravelPropertyData\Enums\FinishQuality;

// Development feasibility calculator
$feasibility = PropertyData::developmentCalculator(
    postcode: 'SW1A 1AA',
    purchasePrice: 500000,
    sqftPreDevelopment: 1000,
    sqftPostDevelopment: 1500,
    projectType: ProjectType::REFURBISH,
    finishQuality: FinishQuality::PREMIUM,
    town: 'London' // optional
);

// Gross Development Value estimate
$gdv = PropertyData::developmentGdv(
    postcode: 'SW1A 1AA',
    town: 'London',
    flat0: 0,        // Studio flats
    flat1: 2,        // 1-bed flats
    flat2: 3,        // 2-bed flats
    flat3: 1,        // 3-bed flats
    flat4: 0,        // 4-bed flats
    terracedHouse2: 0,  // 2-bed terraced houses
    terracedHouse3: 2,  // 3-bed terraced houses
    terracedHouse4: 1,  // 4-bed terraced houses
    terracedHouse5: 0   // 5-bed terraced houses
);

// Build cost data
$buildCost = PropertyData::buildCost('SW1A 1AA');

// Rebuild cost calculator
$rebuildCost = PropertyData::rebuildCost(
    postcode: 'SW1A 1AA',
    internalArea: 1200,
    siteQuality: 'good',
    propertyType: 'detached',
    complexity: 'standard',
    storeys: 2
);
```

### 📋 Planning & Regulatory Methods

```php
use Lanos\LaravelPropertyData\Enums\DecisionRating;

// Planning applications
$planning = PropertyData::planningApplications(
    postcode: 'SW1A 1AA',
    decisionRating: DecisionRating::POSITIVE,
    category: 'residential',
    maxAge: 365
);

// Check if in Area of Outstanding Natural Beauty
$aonb = PropertyData::aonb(postcode: 'SW1A 1AA');

// Check if in conservation area
$conservation = PropertyData::conservationArea(postcode: 'SW1A 1AA');

// Check if in green belt
$greenBelt = PropertyData::greenBelt(postcode: 'SW1A 1AA');

// Check if in national park
$nationalPark = PropertyData::nationalPark(postcode: 'SW1A 1AA');

// Get listed buildings
$listedBuildings = PropertyData::listedBuildings('SW1A 1AA');
```

### 📊 Area Information Methods

```php
// Area type classification (rural/urban)
$areaType = PropertyData::areaType('SW1A 1AA');

// Council tax information
$councilTax = PropertyData::councilTax(postcode: 'SW1A 1AA');

// Crime statistics
$crime = PropertyData::crime(postcode: 'SW1A 1AA');

// Demographics
$demographics = PropertyData::demographics(postcode: 'SW1A 1AA');

// Flood risk
$floodRisk = PropertyData::floodRisk(postcode: 'SW1A 1AA');

// Household income
$income = PropertyData::householdIncome('SW1A 1AA');

// Internet speed
$internetSpeed = PropertyData::internetSpeed('SW1A 1AA');

// Local Housing Allowance rate
$lhaRate = PropertyData::lhaRate('SW1A 1AA');

// Population statistics
$population = PropertyData::population('SW1A 1AA');

// Political representation
$politics = PropertyData::politics('SW1A 1AA');

// PTAL score (London only)
$ptal = PropertyData::ptal('SW1A 1AA');

// Key statistics by postcode
$keyStats = PropertyData::postcodeKeyStats('SW1A 1AA');
```

### 🏪 Amenities Methods

```php
// Nearby restaurants
$restaurants = PropertyData::restaurants(
    postcode: 'SW1A 1AA',
    page: 1,
    perPage: 20
);

// Nearby schools
$schools = PropertyData::schools(postcode: 'SW1A 1AA');
```

### 📄 Document Methods

```php
// Land Registry documents
$documents = PropertyData::landRegistryDocuments(
    title: 'DN123456',
    uprn: '123456789012'
);

// Site plan documents
$sitePlans = PropertyData::sitePlanDocuments('SW1A 1AA', page: 1, perPage: 20);
```

### 💳 Financial Calculators

```php
// Mortgage calculator
$mortgage = PropertyData::mortgageCalculator(
    price: 500000,
    deposit: 100000,
    rate: 5.5,
    termYears: 25
);

// Current mortgage rates
$rates = PropertyData::mortgageRates();

// Stamp Duty calculator
$stampDuty = PropertyData::stampDutyCalculator(
    price: 500000,
    buyerType: 'first-time'
);
```

### 🔧 Account Methods

```php
// Get API credits information
$credits = PropertyData::accountCredits();

// Get account documents
$documents = PropertyData::accountDocuments(page: 1, perPage: 20);

// Test API connection
$isConnected = PropertyData::testConnection();
```

## Usage Examples

### Example 1: Property Investment Analysis

```php
use PropertyData;
use Lanos\LaravelPropertyData\Enums\ProjectType;
use Lanos\LaravelPropertyData\Enums\FinishQuality;

class PropertyInvestmentService
{
    public function analyzeInvestment(string $postcode, float $purchasePrice)
    {
        // Get current market data
        $prices = PropertyData::prices($postcode);
        $yields = PropertyData::yields($postcode);
        $demand = PropertyData::demand(postcode: $postcode);
        
        // Get area information
        $crime = PropertyData::crime(postcode: $postcode);
        $schools = PropertyData::schools(postcode: $postcode);
        $demographics = PropertyData::demographics(postcode: $postcode);
        
        // Calculate development potential
        $feasibility = PropertyData::developmentCalculator(
            postcode: $postcode,
            purchasePrice: $purchasePrice,
            sqftPreDevelopment: 1000,
            sqftPostDevelopment: 1400,
            projectType: ProjectType::REFURBISH,
            finishQuality: FinishQuality::MEDIUM
        );
        
        return [
            'market' => [
                'prices' => $prices,
                'yields' => $yields,
                'demand' => $demand,
            ],
            'area' => [
                'crime' => $crime,
                'schools' => $schools,
                'demographics' => $demographics,
            ],
            'development' => $feasibility,
        ];
    }
}
```

### Example 2: Property Due Diligence

```php
use PropertyData;

class PropertyDueDiligenceService
{
    public function performDueDiligence(string $uprn)
    {
        // Get property details
        $property = PropertyData::uprn($uprn);
        $title = PropertyData::uprnTitle($uprn);
        
        // Get planning information
        $planning = PropertyData::planningApplications(
            postcode: $property['postcode']
        );
        
        // Check restrictions
        $checks = [
            'conservation_area' => PropertyData::conservationArea(
                postcode: $property['postcode']
            ),
            'green_belt' => PropertyData::greenBelt(
                postcode: $property['postcode']
            ),
            'listed_buildings' => PropertyData::listedBuildings(
                $property['postcode']
            ),
            'flood_risk' => PropertyData::floodRisk(
                postcode: $property['postcode']
            ),
        ];
        
        // Get documents
        $documents = PropertyData::landRegistryDocuments(
            uprn: $uprn
        );
        
        return [
            'property' => $property,
            'title' => $title,
            'planning' => $planning,
            'restrictions' => $checks,
            'documents' => $documents,
        ];
    }
}
```

### Example 3: Location-Based Search

```php
use PropertyData;

class LocationSearchService
{
    public function searchByCoordinates(float $lat, float $lng)
    {
        $location = "$lat,$lng";
        
        // Get area data using coordinates
        $demand = PropertyData::demand(location: $location);
        $agents = PropertyData::agents(location: $location);
        $crime = PropertyData::crime(location: $location);
        $restaurants = PropertyData::restaurants(location: $location);
        $schools = PropertyData::schools(location: $location);
        
        return compact('demand', 'agents', 'crime', 'restaurants', 'schools');
    }
    
    public function searchByWhat3Words(string $w3w)
    {
        // Get area data using What3Words
        $demand = PropertyData::demand(w3w: $w3w);
        $planning = PropertyData::planningApplications(w3w: $w3w);
        
        return compact('demand', 'planning');
    }
}
```

## Error Handling

The package includes comprehensive error handling with specific exception types:

### Exception Types

- `PropertyDataApiException` - Base exception for all API errors
- `PropertyDataAuthenticationException` - Invalid API key or authentication failed
- `PropertyDataConnectionException` - Network connection issues
- `PropertyDataServerException` - API server errors (5xx responses)
- `PropertyDataRateLimitException` - Rate limit exceeded (currently unused as API has no rate limits)

### Handling Exceptions

```php
use Lanos\LaravelPropertyData\Exceptions\PropertyDataAuthenticationException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataConnectionException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataApiException;

try {
    $data = PropertyData::prices('SW1A 1AA');
} catch (PropertyDataAuthenticationException $e) {
    // Handle authentication error
    Log::error('Invalid API key: ' . $e->getMessage());
} catch (PropertyDataConnectionException $e) {
    // Handle connection error
    Log::error('Connection failed: ' . $e->getMessage());
} catch (PropertyDataApiException $e) {
    // Handle general API error
    Log::error('API error: ' . $e->getMessage());
}
```

### Input Validation

The package validates all inputs before making API calls:

```php
try {
    // This will throw an InvalidArgumentException
    $data = PropertyData::prices('INVALID_POSTCODE');
} catch (\InvalidArgumentException $e) {
    // Handle validation error
    echo $e->getMessage(); // "Invalid UK postcode format..."
}
```

### Automatic Retry Logic

The package automatically retries failed requests for transient errors:

- Connection failures
- Server errors (5xx responses)
- Uses exponential backoff (1s, 2s, 4s...)
- Maximum 3 attempts by default
- Configurable via environment variables

## Testing

### Running Tests

```bash
composer test
```

### Test Coverage

```bash
composer test-coverage
```

### Static Analysis

```bash
composer analyse
```

### Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details on how to contribute to this package.

### Development Setup

1. Fork the repository
2. Clone your fork
3. Install dependencies: `composer install`
4. Create a feature branch: `git checkout -b feature/my-feature`
5. Make your changes
6. Run tests: `composer test`
7. Submit a pull request

## Security

If you discover any security-related issues, please email contact@l4nos.dev instead of using the issue tracker.

## Credits

- [L4nos](https://github.com/l4nos)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Disclaimer

⚠️ **IMPORTANT NOTICE**

This package is an independent integration library for the PropertyData API. We are not affiliated with, endorsed by, or connected to PropertyData or its parent company. 

- This package serves solely as a client interface to integrate with the PropertyData API
- We do not own, operate, or have any control over the PropertyData API
- All property data, valuations, and information are provided by PropertyData
- We are not responsible for the accuracy, completeness, or reliability of data returned by the API
- Users must obtain their own API key directly from PropertyData

For official API documentation and support, please visit [https://propertydata.co.uk](https://propertydata.co.uk)

### Data Usage

Please ensure you comply with PropertyData's terms of service and data usage policies when using this package. The package authors assume no responsibility for how you use the data obtained through the API.

### No Warranty

This package is provided "as is" without warranty of any kind, either expressed or implied. See the LICENSE file for full disclaimer of warranties.