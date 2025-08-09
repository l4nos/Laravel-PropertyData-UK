<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Traits;

use InvalidArgumentException;

trait ValidatesInput
{
    /**
     * Validate UK postcode format.
     *
     * @throws InvalidArgumentException
     */
    protected function validatePostcode(string $postcode): void
    {
        // UK postcode regex pattern - handles full postcodes, districts and sectors
        $patterns = [
            // Full postcode (e.g., SW1A 1AA)
            '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
            // Postcode district (e.g., SW1A)
            '/^[A-Z]{1,2}\d[A-Z\d]?$/i',
            // Postcode sector (e.g., SW1A 1)
            '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d$/i',
        ];

        $valid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $postcode)) {
                $valid = true;
                break;
            }
        }

        if (! $valid) {
            throw new InvalidArgumentException(
                'Invalid UK postcode format. Expected formats: "SW1A 1AA" (full), "SW1A" (district), or "SW1A 1" (sector)'
            );
        }
    }

    /**
     * Validate location coordinates format.
     *
     * @throws InvalidArgumentException
     */
    protected function validateLocation(string $location): void
    {
        $pattern = '/^-?\d+(?:\.\d+)?,\s?-?\d+(?:\.\d+)?$/';

        if (! preg_match($pattern, $location)) {
            throw new InvalidArgumentException(
                'Invalid location format. Expected: lat,lng (e.g., 51.501,-0.141)'
            );
        }

        // Additional validation for valid coordinate ranges
        $coords = explode(',', str_replace(' ', '', $location));
        $lat = (float) $coords[0];
        $lng = (float) $coords[1];

        if ($lat < -90 || $lat > 90) {
            throw new InvalidArgumentException(
                'Invalid latitude. Must be between -90 and 90 degrees.'
            );
        }

        if ($lng < -180 || $lng > 180) {
            throw new InvalidArgumentException(
                'Invalid longitude. Must be between -180 and 180 degrees.'
            );
        }
    }

    /**
     * Validate What3Words format.
     *
     * @throws InvalidArgumentException
     */
    protected function validateW3w(string $w3w): void
    {
        // What3Words format: word.word.word
        $pattern = '/^[a-z]+\.[a-z]+\.[a-z]+$/i';

        if (! preg_match($pattern, $w3w)) {
            throw new InvalidArgumentException(
                'Invalid What3Words format. Expected: word.word.word (e.g., pretty.needed.chill)'
            );
        }
    }

    /**
     * Validate that at least one location parameter is provided.
     *
     * @param  array  $params  Array containing postcode, location, w3w, and/or town
     *
     * @throws InvalidArgumentException
     */
    protected function validateLocationRequired(array $params): void
    {
        $locationParams = ['postcode', 'location', 'w3w', 'town'];
        $hasLocation = false;

        foreach ($locationParams as $param) {
            if (! empty($params[$param])) {
                $hasLocation = true;
                break;
            }
        }

        if (! $hasLocation) {
            throw new InvalidArgumentException(
                'At least one location parameter is required: postcode, location (lat,lng), w3w, or town'
            );
        }
    }

    /**
     * Validate and sanitize optional location parameters.
     *
     * @throws InvalidArgumentException
     */
    protected function validateLocationParams(array $params): array
    {
        $validated = [];

        if (! empty($params['postcode'])) {
            $this->validatePostcode($params['postcode']);
            $validated['postcode'] = $params['postcode'];
        }

        if (! empty($params['location'])) {
            $this->validateLocation($params['location']);
            $validated['location'] = $params['location'];
        }

        if (! empty($params['w3w'])) {
            $this->validateW3w($params['w3w']);
            $validated['w3w'] = $params['w3w'];
        }

        if (! empty($params['town'])) {
            // Basic town validation - just ensure it's not too long and contains valid characters
            if (strlen($params['town']) > 100) {
                throw new InvalidArgumentException('Town name is too long (max 100 characters)');
            }
            if (! preg_match('/^[a-zA-Z\s\-\']+$/', $params['town'])) {
                throw new InvalidArgumentException('Town name contains invalid characters');
            }
            $validated['town'] = $params['town'];
        }

        return $validated;
    }

    /**
     * Validate pagination parameters.
     *
     * @throws InvalidArgumentException
     */
    protected function validatePagination(int $page, int $perPage): void
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page number must be at least 1');
        }

        if ($perPage < 1) {
            throw new InvalidArgumentException('Items per page must be at least 1');
        }

        if ($perPage > 100) {
            throw new InvalidArgumentException('Items per page cannot exceed 100');
        }
    }

    /**
     * Validate address format.
     *
     * @throws InvalidArgumentException
     */
    protected function validateAddress(string $address): void
    {
        if (empty(trim($address))) {
            throw new InvalidArgumentException('Address cannot be empty');
        }

        if (strlen($address) > 500) {
            throw new InvalidArgumentException('Address is too long (max 500 characters)');
        }
    }

    /**
     * Validate UPRN format.
     *
     * @throws InvalidArgumentException
     */
    protected function validateUprn(string $uprn): void
    {
        // UPRNs are numeric strings up to 12 digits
        if (! preg_match('/^\d{1,12}$/', $uprn)) {
            throw new InvalidArgumentException(
                'Invalid UPRN format. Expected numeric string up to 12 digits'
            );
        }
    }

    /**
     * Validate Land Registry title number format.
     *
     * @throws InvalidArgumentException
     */
    protected function validateTitle(string $title): void
    {
        // Title numbers are alphanumeric, typically 2-3 letters followed by numbers
        if (! preg_match('/^[A-Z]{1,3}\d{1,8}$/i', $title)) {
            throw new InvalidArgumentException(
                'Invalid title number format. Expected format like "DN123456"'
            );
        }
    }

    /**
     * Validate positive number.
     *
     * @throws InvalidArgumentException
     */
    protected function validatePositiveNumber(float $value, string $fieldName): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException(
                sprintf('%s must be a positive number', $fieldName)
            );
        }
    }

    /**
     * Validate non-negative number.
     *
     * @throws InvalidArgumentException
     */
    protected function validateNonNegativeNumber(float $value, string $fieldName): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(
                sprintf('%s cannot be negative', $fieldName)
            );
        }
    }

    /**
     * Validate planning application max age.
     *
     * @throws InvalidArgumentException
     */
    protected function validateMaxAge(int $maxAge): void
    {
        if ($maxAge < 14 || $maxAge > 1500) {
            throw new InvalidArgumentException(
                'Max age must be between 14 and 1500 days'
            );
        }
    }

    /**
     * Validate storeys parameter.
     *
     * @throws InvalidArgumentException
     */
    protected function validateStoreys(int $storeys): void
    {
        if ($storeys < 1 || $storeys > 4) {
            throw new InvalidArgumentException(
                'Number of storeys must be between 1 and 4'
            );
        }
    }

    /**
     * Validate internal area minimum.
     *
     * @throws InvalidArgumentException
     */
    protected function validateInternalArea(float $area): void
    {
        if ($area < 300) {
            throw new InvalidArgumentException(
                'Internal area must be at least 300 square feet'
            );
        }
    }
}
