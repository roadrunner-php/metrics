<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;

class Metrics extends AbstractMetrics
{
    public function add(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Add', \compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sub(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Sub', \compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function observe(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Observe', \compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Set', \compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function declare(string $name, CollectorInterface $collector): void
    {
        try {
            $this->rpc->call('Declare', [
                'name' => $name,
                'collector' => $collector->toArray(),
            ]);
        } catch (ServiceException $e) {
            if (\str_contains($e->getMessage(), 'tried to register existing collector')) {
                // suppress duplicate metric error
                return;
            }

            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function unregister(string $name): void
    {
        try {
            $this->rpc->call('Unregister', $name);
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
