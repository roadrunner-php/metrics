<?php

namespace Spiral\RoadRunner\Metrics;

use Spiral\RoadRunner\Metrics\Exception\MetricsException;

class RetryMetrics implements MetricsInterface
{
    /**
     * @param int<0, max> $retryAttempts
     * @param int<0, max> $retrySleepMicroseconds
     */
    public function __construct(
        private readonly MetricsInterface $metrics,
        private readonly int $retryAttempts,
        private readonly int $retrySleepMicroseconds,
    ) {
    }

    public function add(string $name, float $value, array $labels = []): void
    {
        $this->retry(fn () => $this->metrics->add($name, $value, $labels));
    }

    public function sub(string $name, float $value, array $labels = []): void
    {
        $this->retry(fn () => $this->metrics->sub($name, $value, $labels));
    }

    public function observe(string $name, float $value, array $labels = []): void
    {
        $this->retry(fn () => $this->metrics->observe($name, $value, $labels));
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        $this->retry(fn () => $this->metrics->set($name, $value, $labels));
    }

    public function declare(string $name, CollectorInterface $collector): void
    {
        $this->retry(fn () => $this->metrics->declare($name, $collector));
    }

    public function unregister(string $name): void
    {
        $this->retry(fn () => $this->metrics->unregister($name));
    }

    private function retry(callable $request): void
    {
        // 1 + retryAttempts
        $attempts = -1;

        do {
            try {
                $request();

                return;
            } catch (MetricsException $e) {
                if (++$attempts === $this->retryAttempts) {
                    throw $e;
                }
            }

            usleep($this->retrySleepMicroseconds);
        } while ($attempts < $this->retryAttempts);
    }
}
