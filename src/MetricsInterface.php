<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\RoadRunner\Metrics\Exception\MetricsException;

interface MetricsInterface
{
    /**
     * Add collector value. Fallback to appropriate method of related collector.
     *
     * @param non-empty-string $name
     * @param non-empty-string[] $labels
     * @throws MetricsException
     */
    public function add(string $name, float $value, array $labels = []): void;

    /**
     * Subtract the collector value, only for gauge collector.
     *
     * @param non-empty-string $name
     * @param non-empty-string[] $labels
     * @throws MetricsException
     */
    public function sub(string $name, float $value, array $labels = []): void;

    /**
     * Observe collector value, only for histogram and summary collectors.
     *
     * @param non-empty-string $name
     * @param non-empty-string[] $labels
     * @throws MetricsException
     */
    public function observe(string $name, float $value, array $labels = []): void;

    /**
     * Set collector value, only for gauge collector.
     *
     * @param non-empty-string $name
     * @param non-empty-string[] $labels
     * @throws MetricsException
     */
    public function set(string $name, float $value, array $labels = []): void;

    /**
     * Declares named collector.
     *
     * @param non-empty-string $name Collector name.
     *
     * @throws MetricsException
     */
    public function declare(string $name, CollectorInterface $collector): void;

    /**
     * Unregisters named collector.
     *
     * @param non-empty-string $name Collector name.
     *
     * @throws MetricsException
     */
    public function unregister(string $name): void;
}
