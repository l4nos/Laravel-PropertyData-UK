<?php

declare(strict_types=1);

namespace Lanos\LaravelPropertyData\Traits;

use Lanos\LaravelPropertyData\Exceptions\PropertyDataConnectionException;
use Lanos\LaravelPropertyData\Exceptions\PropertyDataServerException;
use Throwable;

trait HandlesRetries
{
    /**
     * Maximum number of retry attempts.
     */
    protected int $maxRetryAttempts = 3;

    /**
     * Base delay between retries in seconds.
     */
    protected int $retryDelay = 1;

    /**
     * Whether retries are enabled.
     */
    protected bool $retryEnabled = true;

    /**
     * Execute a callback with retry logic for transient failures.
     *
     * @param callable $callback The callback to execute
     * @param int|null $maxAttempts Maximum number of attempts (null to use default)
     * @return mixed The result of the callback
     * @throws Throwable
     */
    protected function executeWithRetry(callable $callback, ?int $maxAttempts = null): mixed
    {
        if (!$this->retryEnabled) {
            return $callback();
        }

        $maxAttempts = $maxAttempts ?? $this->maxRetryAttempts;
        $attempts = 0;
        $lastException = null;

        while ($attempts < $maxAttempts) {
            try {
                return $callback();
            } catch (PropertyDataConnectionException | PropertyDataServerException $e) {
                $lastException = $e;
                $attempts++;

                if ($attempts < $maxAttempts) {
                    $this->handleRetryDelay($attempts);
                    $this->logRetryAttempt($attempts, $maxAttempts, $e);
                }
            } catch (Throwable $e) {
                // Don't retry for other types of exceptions
                throw $e;
            }
        }

        if ($lastException !== null) {
            $this->logRetryFailure($attempts, $lastException);
            throw $lastException;
        }

        throw new \RuntimeException('Unexpected error in retry logic');
    }

    /**
     * Handle the delay between retry attempts.
     *
     * @param int $attempt Current attempt number
     */
    protected function handleRetryDelay(int $attempt): void
    {
        // Exponential backoff: delay = base * 2^(attempt - 1)
        $delay = $this->retryDelay * pow(2, $attempt - 1);
        
        // Cap the delay at 30 seconds
        $delay = min($delay, 30);
        
        sleep((int) $delay);
    }

    /**
     * Log a retry attempt.
     *
     * @param int $attempt Current attempt number
     * @param int $maxAttempts Maximum number of attempts
     * @param Throwable $exception The exception that triggered the retry
     */
    protected function logRetryAttempt(int $attempt, int $maxAttempts, Throwable $exception): void
    {
        if (property_exists($this, 'loggingEnabled') && $this->loggingEnabled) {
            $message = sprintf(
                'Property Data API request failed, retrying (attempt %d/%d): %s',
                $attempt,
                $maxAttempts,
                $exception->getMessage()
            );

            if (method_exists($this, 'logError')) {
                $this->logError($message, $exception);
            }
        }
    }

    /**
     * Log when all retry attempts have failed.
     *
     * @param int $attempts Total number of attempts made
     * @param Throwable $exception The final exception
     */
    protected function logRetryFailure(int $attempts, Throwable $exception): void
    {
        if (property_exists($this, 'loggingEnabled') && $this->loggingEnabled) {
            $message = sprintf(
                'Property Data API request failed after %d attempts: %s',
                $attempts,
                $exception->getMessage()
            );

            if (method_exists($this, 'logError')) {
                $this->logError($message, $exception);
            }
        }
    }

    /**
     * Set the maximum number of retry attempts.
     *
     * @param int $attempts
     * @return self
     */
    public function setMaxRetryAttempts(int $attempts): self
    {
        $this->maxRetryAttempts = max(1, $attempts);
        return $this;
    }

    /**
     * Set the base retry delay in seconds.
     *
     * @param int $seconds
     * @return self
     */
    public function setRetryDelay(int $seconds): self
    {
        $this->retryDelay = max(1, $seconds);
        return $this;
    }

    /**
     * Enable or disable retry logic.
     *
     * @param bool $enabled
     * @return self
     */
    public function setRetryEnabled(bool $enabled): self
    {
        $this->retryEnabled = $enabled;
        return $this;
    }

    /**
     * Configure retry settings from config array.
     *
     * @param array $config
     * @return self
     */
    protected function configureRetries(array $config): self
    {
        if (isset($config['enabled'])) {
            $this->setRetryEnabled((bool) $config['enabled']);
        }

        if (isset($config['max_attempts'])) {
            $this->setMaxRetryAttempts((int) $config['max_attempts']);
        }

        if (isset($config['delay'])) {
            // Convert milliseconds to seconds if needed
            $delay = (int) $config['delay'];
            if ($delay > 100) {
                // Assume it's in milliseconds
                $delay = (int) ceil($delay / 1000);
            }
            $this->setRetryDelay($delay);
        }

        return $this;
    }
}