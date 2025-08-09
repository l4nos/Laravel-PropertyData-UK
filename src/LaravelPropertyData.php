<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData;

use Lanos\LaravelPropertyData\Enums\DecisionRating;
use Lanos\LaravelPropertyData\Enums\FinishQuality;
use Lanos\LaravelPropertyData\Enums\ProjectType;
use Lanos\LaravelPropertyData\Http\PropertyDataClient;
use Lanos\LaravelPropertyData\Traits\ValidatesInput;

class LaravelPropertyData
{
    use ValidatesInput;

    protected PropertyDataClient $client;

    /**
     * Create a new LaravelPropertyData instance.
     */
    public function __construct(?PropertyDataClient $client = null)
    {
        $this->client = $client ?? new PropertyDataClient;
    }

    /**
     * Get the version of the package.
     */
    public function version(): string
    {
        return '1.0.0';
    }

    /**
     * Test the API connection.
     */
    public function testConnection(): bool
    {
        return $this->client->testConnection();
    }

    // ===========================================
    // ADDRESS & UPRN METHODS
    // ===========================================

    /**
     * Match address to UPRN.
     */
    public function addressMatchUprn(string $address): array
    {
        $this->validateAddress($address);

        return $this->client->get('address-match-uprn', ['address' => $address]);
    }

    /**
     * Lookup property by UPRN.
     */
    public function uprn(string $uprn): array
    {
        $this->validateUprn($uprn);

        return $this->client->get('uprn', ['uprn' => $uprn]);
    }

    /**
     * Lookup multiple UPRNs.
     */
    public function uprns(array $uprns): array
    {
        foreach ($uprns as $uprn) {
            $this->validateUprn($uprn);
        }

        return $this->client->get('uprns', ['uprns' => implode(',', $uprns)]);
    }

    /**
     * Get Land Registry title(s) for a UPRN.
     */
    public function uprnTitle(string $uprn): array
    {
        $this->validateUprn($uprn);

        return $this->client->get('uprn-title', ['uprn' => $uprn]);
    }

    // ===========================================
    // PROPERTY ANALYSIS METHODS
    // ===========================================

    /**
     * Analyse buildings by title number.
     */
    public function analyseBuildings(string $title): array
    {
        $this->validateTitle($title);

        return $this->client->get('analyse-buildings', ['title' => $title]);
    }

    /**
     * Get title information by title number or UPRN.
     */
    public function title(?string $title = null, ?string $uprn = null): array
    {
        if ($title !== null) {
            $this->validateTitle($title);
        }
        if ($uprn !== null) {
            $this->validateUprn($uprn);
        }

        $params = array_filter([
            'title' => $title,
            'uprn' => $uprn,
        ]);

        return $this->client->get('title', $params);
    }

    /**
     * Get title use class information.
     */
    public function titleUseClass(?string $title = null, ?string $uprn = null): array
    {
        if ($title !== null) {
            $this->validateTitle($title);
        }
        if ($uprn !== null) {
            $this->validateUprn($uprn);
        }

        $params = array_filter([
            'title' => $title,
            'uprn' => $uprn,
        ]);

        return $this->client->get('title-use-class', $params);
    }

    /**
     * Get internal floor areas for properties.
     */
    public function floorAreas(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('floor-areas', ['postcode' => $postcode]);
    }

    /**
     * Get energy efficiency (EPC) information.
     */
    public function energyEfficiency(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('energy-efficiency', ['postcode' => $postcode]);
    }

    // ===========================================
    // MARKET DATA METHODS
    // ===========================================

    /**
     * Get current asking prices and statistics.
     */
    public function prices(string $postcode, int $page = 1, int $perPage = 20): array
    {
        $this->validatePostcode($postcode);
        $this->validatePagination($page, $perPage);

        return $this->client->get('prices', [
            'postcode' => $postcode,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Get prices per square foot.
     */
    public function pricesPerSqf(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('prices-per-sqf', ['postcode' => $postcode]);
    }

    /**
     * Get sold prices from Land Registry.
     */
    public function soldPrices(string $postcode, int $page = 1, int $perPage = 20): array
    {
        $this->validatePostcode($postcode);
        $this->validatePagination($page, $perPage);

        return $this->client->get('sold-prices', [
            'postcode' => $postcode,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Get sold prices per square foot.
     */
    public function soldPricesPerSqf(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('sold-prices-per-sqf', ['postcode' => $postcode]);
    }

    /**
     * Get rental market statistics.
     */
    public function rents(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('rents', ['postcode' => $postcode]);
    }

    /**
     * Get HMO rent estimates.
     */
    public function rentsHmo(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('rents-hmo', ['postcode' => $postcode]);
    }

    /**
     * Get yield calculations.
     */
    public function yields(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('yields', ['postcode' => $postcode]);
    }

    /**
     * Get price growth metrics.
     */
    public function growth(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('growth', ['postcode' => $postcode]);
    }

    /**
     * Get growth in price-per-sqft.
     */
    public function growthPsf(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('growth-psf', ['postcode' => $postcode]);
    }

    /**
     * Get property demand analytics.
     */
    public function demand(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'town' => $town,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('demand', $validated);
    }

    /**
     * Get rental demand analytics.
     */
    public function demandRent(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'town' => $town,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('demand-rent', $validated);
    }

    /**
     * Get estate agent market share.
     */
    public function agents(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null, int $page = 1, int $perPage = 20): array
    {
        $this->validatePagination($page, $perPage);

        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'town' => $town,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        $validated['page'] = $page;
        $validated['per_page'] = $perPage;

        return $this->client->get('agents', $validated);
    }

    // ===========================================
    // VALUATION METHODS
    // ===========================================

    /**
     * Get instant sale valuation.
     */
    public function valuationSale(string $postcode, ?int $beds = null, ?float $sqft = null): array
    {
        $this->validatePostcode($postcode);

        if ($sqft !== null) {
            $this->validatePositiveNumber($sqft, 'Square footage');
        }

        $params = array_filter([
            'postcode' => $postcode,
            'beds' => $beds,
            'sqft' => $sqft,
        ]);

        return $this->client->get('valuation-sale', $params);
    }

    /**
     * Get rent valuation estimate.
     */
    public function valuationRent(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('valuation-rent', ['postcode' => $postcode]);
    }

    /**
     * Get HMO valuation estimate.
     */
    public function valuationHmo(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('valuation-hmo', ['postcode' => $postcode]);
    }

    /**
     * Get historical valuation series.
     */
    public function valuationHistorical(string $postcode, int $page = 1): array
    {
        $this->validatePostcode($postcode);
        $this->validatePagination($page, 20); // Use default per_page

        return $this->client->get('valuation-historical', [
            'postcode' => $postcode,
            'page' => $page,
        ]);
    }

    // ===========================================
    // PLANNING & REGULATORY METHODS
    // ===========================================

    /**
     * Get planning applications.
     */
    public function planningApplications(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?DecisionRating $decisionRating = null, ?string $category = null, ?int $maxAge = null): array
    {
        if ($maxAge !== null) {
            $this->validateMaxAge($maxAge);
        }

        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'decision_rating' => $decisionRating?->value,
            'category' => $category,
            'max_age' => $maxAge,
        ]);

        $validated = $this->validateLocationParams($params);

        if ($decisionRating !== null) {
            $validated['decision_rating'] = $decisionRating->value;
        }
        if ($category !== null) {
            $validated['category'] = $category;
        }
        if ($maxAge !== null) {
            $validated['max_age'] = $maxAge;
        }

        return $this->client->get('planning-applications', $validated);
    }

    /**
     * Check if location is in Area of Outstanding Natural Beauty.
     */
    public function aonb(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'town' => $town,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('aonb', $validated);
    }

    /**
     * Check if location is in a conservation area.
     */
    public function conservationArea(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'town' => $town,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('conservation-area', $validated);
    }

    /**
     * Check if location is in green belt.
     */
    public function greenBelt(?string $postcode = null, ?string $location = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('green-belt', $validated);
    }

    /**
     * Check if location is in a national park.
     */
    public function nationalPark(?string $postcode = null, ?string $location = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('national-park', $validated);
    }

    /**
     * Get listed buildings in area.
     */
    public function listedBuildings(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('listed-buildings', ['postcode' => $postcode]);
    }

    // ===========================================
    // AREA INFORMATION METHODS
    // ===========================================

    /**
     * Get area type classification (rural/urban).
     */
    public function areaType(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('area-type', ['postcode' => $postcode]);
    }

    /**
     * Get council tax band information.
     */
    public function councilTax(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
            'w3w' => $w3w,
            'town' => $town,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('council-tax', $validated);
    }

    /**
     * Get crime statistics.
     */
    public function crime(?string $postcode = null, ?string $location = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('crime', $validated);
    }

    /**
     * Get demographic statistics.
     */
    public function demographics(?string $postcode = null, ?string $location = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('demographics', $validated);
    }

    /**
     * Get flood risk information.
     */
    public function floodRisk(?string $postcode = null, ?string $location = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('flood-risk', $validated);
    }

    /**
     * Get household income metrics.
     */
    public function householdIncome(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('household-income', ['postcode' => $postcode]);
    }

    /**
     * Get internet speed data.
     */
    public function internetSpeed(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('internet-speed', ['postcode' => $postcode]);
    }

    /**
     * Get Local Housing Allowance rate.
     */
    public function lhaRate(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('lha-rate', ['postcode' => $postcode]);
    }

    /**
     * Get population statistics.
     */
    public function population(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('population', ['postcode' => $postcode]);
    }

    /**
     * Get political representation information.
     */
    public function politics(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('politics', ['postcode' => $postcode]);
    }

    /**
     * Get PTAL score (London only).
     */
    public function ptal(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('ptal', ['postcode' => $postcode]);
    }

    /**
     * Get key statistics by postcode.
     */
    public function postcodeKeyStats(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('postcode-key-stats', ['postcode' => $postcode]);
    }

    // ===========================================
    // PROPERTY SEARCH METHODS
    // ===========================================

    /**
     * Get sourced properties list.
     */
    public function sourcedProperties(int $page = 1, int $perPage = 20): array
    {
        $this->validatePagination($page, $perPage);

        return $this->client->get('sourced-properties', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Get single sourced property details.
     */
    public function sourcedProperty(string $id): array
    {
        return $this->client->get('sourced-property', ['id' => $id]);
    }

    // ===========================================
    // AMENITIES METHODS
    // ===========================================

    /**
     * Get nearby restaurants.
     */
    public function restaurants(?string $postcode = null, ?string $location = null, int $page = 1, int $perPage = 20): array
    {
        $this->validatePagination($page, $perPage);

        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        $validated['page'] = $page;
        $validated['per_page'] = $perPage;

        return $this->client->get('restaurants', $validated);
    }

    /**
     * Get nearby schools.
     */
    public function schools(?string $postcode = null, ?string $location = null): array
    {
        $params = array_filter([
            'postcode' => $postcode,
            'location' => $location,
        ]);

        $validated = $this->validateLocationParams($params);
        $this->validateLocationRequired($validated);

        return $this->client->get('schools', $validated);
    }

    // ===========================================
    // DOCUMENT METHODS
    // ===========================================

    /**
     * Get Land Registry documents.
     */
    public function landRegistryDocuments(?string $title = null, ?string $uprn = null): array
    {
        if ($title !== null) {
            $this->validateTitle($title);
        }
        if ($uprn !== null) {
            $this->validateUprn($uprn);
        }

        $params = array_filter([
            'title' => $title,
            'uprn' => $uprn,
        ]);

        return $this->client->get('land-registry-documents', $params);
    }

    /**
     * Get site plan documents.
     */
    public function sitePlanDocuments(string $postcode, int $page = 1, int $perPage = 20): array
    {
        $this->validatePostcode($postcode);
        $this->validatePagination($page, $perPage);

        return $this->client->get('site-plan-documents', [
            'postcode' => $postcode,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    // ===========================================
    // PROPERTY INVESTMENT METHODS
    // ===========================================

    /**
     * Get freehold information.
     */
    public function freeholds(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('freeholds', ['postcode' => $postcode]);
    }

    /**
     * Get National HMO register data.
     */
    public function nationalHmoRegister(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('national-hmo-register', ['postcode' => $postcode]);
    }

    // ===========================================
    // CALCULATOR METHODS
    // ===========================================

    /**
     * Calculate basic development feasibility.
     */
    public function developmentCalculator(
        string $postcode,
        float $purchasePrice,
        float $sqftPreDevelopment,
        float $sqftPostDevelopment,
        ProjectType $projectType,
        FinishQuality $finishQuality,
        ?string $town = null
    ): array {
        $this->validatePostcode($postcode);
        $this->validatePositiveNumber($purchasePrice, 'Purchase price');
        $this->validatePositiveNumber($sqftPreDevelopment, 'Pre-development square footage');
        $this->validatePositiveNumber($sqftPostDevelopment, 'Post-development square footage');

        $params = array_filter([
            'postcode' => $postcode,
            'town' => $town,
            'purchase_price' => $purchasePrice,
            'sqft_pre_development' => $sqftPreDevelopment,
            'sqft_post_development' => $sqftPostDevelopment,
            'project_type' => $projectType->value,
            'finish_quality' => $finishQuality->value,
        ]);

        return $this->client->get('development-calculator', $params);
    }

    /**
     * Estimate Gross Development Value from unit mix.
     */
    public function developmentGdv(
        string $postcode,
        ?string $town = null,
        int $flat0 = 0,
        int $flat1 = 0,
        int $flat2 = 0,
        int $flat3 = 0,
        int $flat4 = 0,
        int $terracedHouse2 = 0,
        int $terracedHouse3 = 0,
        int $terracedHouse4 = 0,
        int $terracedHouse5 = 0
    ): array {
        $this->validatePostcode($postcode);

        $params = array_filter([
            'postcode' => $postcode,
            'town' => $town,
            'flat_0' => $flat0 ?: null,
            'flat_1' => $flat1 ?: null,
            'flat_2' => $flat2 ?: null,
            'flat_3' => $flat3 ?: null,
            'flat_4' => $flat4 ?: null,
            'terraced_house_2' => $terracedHouse2 ?: null,
            'terraced_house_3' => $terracedHouse3 ?: null,
            'terraced_house_4' => $terracedHouse4 ?: null,
            'terraced_house_5' => $terracedHouse5 ?: null,
        ]);

        return $this->client->get('development-gdv', $params);
    }

    /**
     * Calculate mortgage repayments.
     */
    public function mortgageCalculator(?float $price = null, ?float $deposit = null, ?float $rate = null, ?int $termYears = null): array
    {
        if ($price !== null) {
            $this->validatePositiveNumber($price, 'Price');
        }
        if ($deposit !== null) {
            $this->validateNonNegativeNumber($deposit, 'Deposit');
        }
        if ($rate !== null) {
            $this->validatePositiveNumber($rate, 'Interest rate');
        }
        if ($termYears !== null && $termYears <= 0) {
            throw new \InvalidArgumentException('Term years must be positive');
        }

        $params = array_filter([
            'price' => $price,
            'deposit' => $deposit,
            'rate' => $rate,
            'term_years' => $termYears,
        ]);

        return $this->client->get('mortgage-calculator', $params);
    }

    /**
     * Get current mortgage rates.
     */
    public function mortgageRates(): array
    {
        return $this->client->get('mortgage-rates');
    }

    /**
     * Calculate rebuild cost.
     */
    public function rebuildCost(string $postcode, float $internalArea, ?string $siteQuality = null, ?string $propertyType = null, ?string $complexity = null, int $storeys = 2): array
    {
        $this->validatePostcode($postcode);
        $this->validateInternalArea($internalArea);
        $this->validateStoreys($storeys);

        $params = array_filter([
            'postcode' => $postcode,
            'internal_area' => $internalArea,
            'site_quality' => $siteQuality,
            'property_type' => $propertyType,
            'complexity' => $complexity,
            'storeys' => $storeys,
        ]);

        return $this->client->get('rebuild-cost', $params);
    }

    /**
     * Calculate Stamp Duty Land Tax.
     */
    public function stampDutyCalculator(?float $price = null, ?string $buyerType = null): array
    {
        if ($price !== null) {
            $this->validatePositiveNumber($price, 'Price');
        }

        $params = array_filter([
            'price' => $price,
            'buyer_type' => $buyerType,
        ]);

        return $this->client->get('stamp-duty-calculator', $params);
    }

    /**
     * Get build cost data.
     */
    public function buildCost(string $postcode): array
    {
        $this->validatePostcode($postcode);

        return $this->client->get('build-cost', ['postcode' => $postcode]);
    }

    // ===========================================
    // ACCOUNT METHODS
    // ===========================================

    /**
     * Get account credits information.
     */
    public function accountCredits(): array
    {
        return $this->client->get('account/credits');
    }

    /**
     * Get account documents.
     */
    public function accountDocuments(int $page = 1, int $perPage = 20): array
    {
        $this->validatePagination($page, $perPage);

        return $this->client->get('account/documents', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }
}
