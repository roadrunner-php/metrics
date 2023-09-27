<?php

namespace Spiral\RoadRunner\Metrics;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;

class SuppressExceptionsMetrics implements MetricsInterface
{
    public function __construct(
        private readonly MetricsInterface $metrics,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function add(string $name, float $value, array $labels = []): void
    {
        try {
            $this->metrics->add($name, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf('[Metrics] Operation "Add" was failed: %s', $e->getMessage()));
        }
    }

    public function sub(string $name, float $value, array $labels = []): void
    {
        try {
            $this->metrics->sub($name, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf('[Metrics] Operation "Sub" was failed: %s', $e->getMessage()));
        }
    }

    public function observe(string $name, float $value, array $labels = []): void
    {
        try {
            $this->metrics->observe($name, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf('[Metrics] Operation "Observe" was failed: %s', $e->getMessage()));
        }
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        try {
            $this->metrics->set($name, $value, $labels);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf('[Metrics] Operation "Set" was failed: %s', $e->getMessage()));
        }
    }

    public function declare(string $name, CollectorInterface $collector): void
    {
        try {
            $this->metrics->declare($name, $collector);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf('[Metrics] Operation "Declare" was failed: %s', $e->getMessage()));
        }
    }

    public function unregister(string $name): void
    {
        try {
            $this->metrics->unregister($name);
        } catch (MetricsException $e) {
            $this->logger->warning(sprintf('[Metrics] Operation "Unregister" was failed: %s', $e->getMessage()));
        }
    }
}
