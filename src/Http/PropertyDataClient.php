<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataApiException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataAuthenticationException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataConnectionException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataRateLimitException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataServerException;
use Lanos\LaravelPropertyData\Traits\HandlesRetries;
use Psr\Http\Message\ResponseInterface;

class PropertyDataClient
{
    use HandlesRetries;

    protected Client $httpClient;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected bool $loggingEnabled;
    protected ?string $logChannel;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? config('property-data.api.base_url');
        $this->apiKey = $config['key'] ?? config('property-data.api.key');
        $this->timeout = $config['timeout'] ?? config('property-data.api.timeout');
        $this->loggingEnabled = $config['logging_enabled'] ?? config('property-data.logging.enabled', true);
        $this->logChannel = $config['log_channel'] ?? config('property-data.logging.channel');

        $this->validateConfig();
        $this->initializeHttpClient();
        
        // Configure retry settings
        $retryConfig = $config['retry'] ?? config('property-data.api.retry', []);
        $this->configureRetries($retryConfig);
    }

    /**
     * Make a GET request to the Property Data API.
     *
     * @param string $endpoint The API endpoint (without leading slash)
     * @param array $queryParams Additional query parameters
     * @return array The decoded JSON response
     * @throws PropertyDataApiException
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        // Add API key to query parameters
        $queryParams['api_key'] = $this->apiKey;

        $url = $this->buildUrl($endpoint);

        // Execute with retry logic for transient failures
        return $this->executeWithRetry(function () use ($url, $queryParams) {
            try {
                $this->logRequest('GET', $url, $queryParams);

                $response = $this->httpClient->get($url, [
                    'query' => $queryParams,
                ]);

                $data = $this->parseResponse($response);
                
                $this->logResponse($url, $response->getStatusCode(), $data);

                return $data;

            } catch (ConnectException $e) {
                $this->logError('Connection error', $e);
                throw new PropertyDataConnectionException(
                    'Unable to connect to Property Data API: ' . $e->getMessage(),
                    0,
                    $e
                );
            } catch (ClientException $e) {
                $this->handleClientException($e);
                throw new PropertyDataApiException('Unhandled client exception'); // Should never reach here
            } catch (ServerException $e) {
                $this->logError('Server error', $e);
                $response = $e->getResponse();
                throw new PropertyDataServerException(
                    'Property Data API server error: ' . $e->getMessage(),
                    $response->getStatusCode(),
                    $e
                );
            } catch (RequestException $e) {
                $this->logError('Request error', $e);
                $response = $e->getResponse();
                $statusCode = $response ? $response->getStatusCode() : 0;
                throw new PropertyDataApiException(
                    'Property Data API request failed: ' . $e->getMessage(),
                    $statusCode,
                    $e
                );
            }
        });
    }

    /**
     * Get account credits information.
     *
     * @return array
     * @throws PropertyDataApiException
     */
    public function getAccountCredits(): array
    {
        return $this->get('account/credits');
    }

    /**
     * Test the API connection and authentication.
     *
     * @return bool
     * @throws PropertyDataApiException
     */
    public function testConnection(): bool
    {
        try {
            $this->getAccountCredits();
            return true;
        } catch (PropertyDataAuthenticationException) {
            return false;
        }
    }

    /**
     * Validate the configuration.
     *
     * @throws PropertyDataApiException
     */
    protected function validateConfig(): void
    {
        if (empty($this->baseUrl)) {
            throw new PropertyDataApiException('Property Data API base URL is not configured');
        }

        if (empty($this->apiKey)) {
            throw new PropertyDataApiException('Property Data API key is not configured');
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new PropertyDataApiException('Invalid Property Data API base URL format');
        }
    }

    /**
     * Initialize the HTTP client.
     */
    protected function initializeHttpClient(): void
    {
        $this->httpClient = new Client([
            'timeout' => $this->timeout,
            'connect_timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Laravel-Property-Data/1.0.0',
            ],
        ]);
    }

    /**
     * Build the full URL for an endpoint.
     *
     * @param string $endpoint
     * @return string
     */
    protected function buildUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Parse the API response.
     *
     * @param ResponseInterface $response
     * @return array
     * @throws PropertyDataApiException
     */
    protected function parseResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();

        if (empty($content)) {
            throw new PropertyDataApiException('Empty response from Property Data API');
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PropertyDataApiException(
                'Invalid JSON response from Property Data API: ' . json_last_error_msg()
            );
        }

        if (!is_array($data)) {
            throw new PropertyDataApiException(
                'Property Data API response is not a valid array'
            );
        }

        return $data;
    }

    /**
     * Handle client exceptions (4xx errors).
     *
     * @param ClientException $e
     * @throws PropertyDataApiException
     */
    protected function handleClientException(ClientException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $message = $e->getMessage();

        $this->logError('Client error', $e);

        switch ($statusCode) {
            case 401:
                throw new PropertyDataAuthenticationException(
                    'Invalid Property Data API key or authentication failed',
                    401,
                    $e
                );
            case 429:
                throw new PropertyDataRateLimitException(
                    'Property Data API rate limit exceeded',
                    429,
                    $e
                );
            default:
                throw new PropertyDataApiException(
                    "Property Data API client error: {$message}",
                    $statusCode,
                    $e
                );
        }
    }

    /**
     * Log API request.
     *
     * @param string $method
     * @param string $url
     * @param array $params
     */
    protected function logRequest(string $method, string $url, array $params): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        // Remove API key from logged parameters for security
        $logParams = $params;
        if (isset($logParams['api_key'])) {
            $logParams['api_key'] = '***HIDDEN***';
        }

        Log::channel($this->logChannel)->info('Property Data API Request', [
            'method' => $method,
            'url' => $url,
            'parameters' => $logParams,
        ]);
    }

    /**
     * Log API response.
     *
     * @param string $url
     * @param int $statusCode
     * @param array $data
     */
    protected function logResponse(string $url, int $statusCode, array $data): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $responseJson = json_encode($data);
        Log::channel($this->logChannel)->info('Property Data API Response', [
            'url' => $url,
            'status_code' => $statusCode,
            'response_size' => $responseJson !== false ? strlen($responseJson) : 0,
        ]);
    }

    /**
     * Log API error.
     *
     * @param string $message
     * @param \Throwable $exception
     */
    protected function logError(string $message, \Throwable $exception): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        Log::channel($this->logChannel)->error("Property Data API Error: {$message}", [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}