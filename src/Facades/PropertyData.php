<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Property Data Facade
 * 
 * @method static string version()
 * @method static bool testConnection()
 * 
 * @method static array addressMatchUprn(string $address)
 * @method static array uprn(string $uprn)
 * @method static array uprns(array $uprns)
 * @method static array uprnTitle(string $uprn)
 * 
 * @method static array analyseBuildings(string $title)
 * @method static array title(?string $title = null, ?string $uprn = null)
 * @method static array titleUseClass(?string $title = null, ?string $uprn = null)
 * @method static array floorAreas(string $postcode)
 * @method static array energyEfficiency(string $postcode)
 * 
 * @method static array prices(string $postcode, int $page = 1, int $perPage = 20)
 * @method static array pricesPerSqf(string $postcode)
 * @method static array soldPrices(string $postcode, int $page = 1, int $perPage = 20)
 * @method static array soldPricesPerSqf(string $postcode)
 * @method static array rents(string $postcode)
 * @method static array rentsHmo(string $postcode)
 * @method static array yields(string $postcode)
 * @method static array growth(string $postcode)
 * @method static array growthPsf(string $postcode)
 * @method static array demand(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null)
 * @method static array demandRent(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null)
 * @method static array agents(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null, int $page = 1, int $perPage = 20)
 * 
 * @method static array valuationSale(string $postcode, ?int $beds = null, ?float $sqft = null)
 * @method static array valuationRent(string $postcode)
 * @method static array valuationHmo(string $postcode)
 * @method static array valuationHistorical(string $postcode, int $page = 1)
 * 
 * @method static array planningApplications(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $decisionRating = null, ?string $category = null, ?int $maxAge = null)
 * @method static array aonb(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null)
 * @method static array conservationArea(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null)
 * @method static array greenBelt(?string $postcode = null, ?string $location = null)
 * @method static array nationalPark(?string $postcode = null, ?string $location = null)
 * @method static array listedBuildings(string $postcode)
 * 
 * @method static array areaType(string $postcode)
 * @method static array councilTax(?string $postcode = null, ?string $location = null, ?string $w3w = null, ?string $town = null)
 * @method static array crime(?string $postcode = null, ?string $location = null)
 * @method static array demographics(?string $postcode = null, ?string $location = null)
 * @method static array floodRisk(?string $postcode = null, ?string $location = null)
 * @method static array householdIncome(string $postcode)
 * @method static array internetSpeed(string $postcode)
 * @method static array lhaRate(string $postcode)
 * @method static array population(string $postcode)
 * @method static array politics(string $postcode)
 * @method static array ptal(string $postcode)
 * @method static array postcodeKeyStats(string $postcode)
 * 
 * @method static array sourcedProperties(int $page = 1, int $perPage = 20)
 * @method static array sourcedProperty(string $id)
 * 
 * @method static array restaurants(?string $postcode = null, ?string $location = null, int $page = 1, int $perPage = 20)
 * @method static array schools(?string $postcode = null, ?string $location = null)
 * 
 * @method static array landRegistryDocuments(?string $title = null, ?string $uprn = null)
 * @method static array sitePlanDocuments(string $postcode, int $page = 1, int $perPage = 20)
 * 
 * @method static array freeholds(string $postcode)
 * @method static array nationalHmoRegister(string $postcode)
 * 
 * @method static array developmentCalculator(string $postcode, float $purchasePrice, float $sqftPreDevelopment, float $sqftPostDevelopment, string $projectType, string $finishQuality, ?string $town = null)
 * @method static array developmentGdv(string $postcode, ?string $town = null, array $unitMix = [])
 * @method static array mortgageCalculator(?float $price = null, ?float $deposit = null, ?float $rate = null, ?int $termYears = null)
 * @method static array mortgageRates()
 * @method static array rebuildCost(string $postcode, float $internalArea, ?string $siteQuality = null, ?string $propertyType = null, ?string $complexity = null, int $storeys = 2)
 * @method static array stampDutyCalculator(?float $price = null, ?string $buyerType = null)
 * @method static array buildCost(string $postcode)
 * 
 * @method static array accountCredits()
 * @method static array accountDocuments(int $page = 1, int $perPage = 20)
 * 
 * @see \Lanos\LaravelPropertyData\LaravelPropertyData
 */
class PropertyData extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-property-data';
    }
}