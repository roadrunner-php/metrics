<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use function compact;
use function str_contains;

class Metrics extends AbstractMetrics
{
    public function __construct(
        protected readonly RPCInterface $rpc
    ) {
    }

    public function add(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('metrics.Add', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sub(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('metrics.Sub', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function observe(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('metrics.Observe', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('metrics.Set', compact('name', 'value', 'labels'));
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
