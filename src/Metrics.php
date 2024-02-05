<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use function compact;
use function str_contains;

class Metrics implements MetricsInterface
{
    private const SERVICE_NAME = 'metrics';

    private readonly RPCInterface $rpc;

    public function __construct(RPCInterface $rpc)
    {
        $this->rpc = $rpc->withServicePrefix(self::SERVICE_NAME);
    }

    private function wrappedCall(string $method, mixed $payload): void
    {
        if ($this->rpc instanceof AsyncRPCInterface) {
            $this->rpc->callIgnoreResponse($method, $payload);
        } else {
            $this->rpc->call($method, $payload);
        }
    }

    public function add(string $name, float $value, array $labels = []): void
    {
        try {
            $this->wrappedCall('Add', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sub(string $name, float $value, array $labels = []): void
    {
        try {
            $this->wrappedCall('Sub', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function observe(string $name, float $value, array $labels = []): void
    {
        try {
            $this->wrappedCall('Observe', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $name, float $value, array $labels = []): void
    {
        try {
            $this->wrappedCall('Set', compact('name', 'value', 'labels'));
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function declare(string $name, CollectorInterface $collector): void
    {
        try {
            $this->wrappedCall('Declare', [
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
            $this->wrappedCall('Unregister', $name);
        } catch (ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
