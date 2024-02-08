<?php

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;

class MetricsIgnoreResponse extends AbstractMetrics
{
    public function __construct(
        protected readonly AsyncRPCInterface $rpc
    ) {
    }

    public function add(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->callIgnoreResponse('metrics.Add', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sub(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->callIgnoreResponse('metrics.Sub', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function observe(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->callIgnoreResponse('metrics.Observe', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->callIgnoreResponse('metrics.Set', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function declare(string $name, CollectorInterface $collector): void
    {
        try {
            $this->rpc->call('metrics.Declare', [
                'name' => $name,
                'collector' => $collector->toArray(),
            ]);
        } catch (ServiceException $e) {
            if (str_contains($e->getMessage(), 'tried to register existing collector')) {
                // suppress duplicate metric error
                return;
            }

            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function unregister(string $name): void
    {
        try {
            $this->rpc->call('metrics.Unregister', $name);
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
