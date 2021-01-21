<?php

/**
 * High-performance PHP process supervisor and load balancer written in Go. Http core.
 */

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;

class Metrics implements MetricsInterface
{
    private const SERVICE_NAME = 'metrics';

    /** @var RPC\RPCInterface */
    private RPC\RPCInterface $rpc;

    /**
     * @param RPC\RPCInterface $rpc
     */
    public function __construct(RPC\RPCInterface $rpc)
    {
        $this->rpc = $rpc->withServicePrefix(self::SERVICE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function add(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Add', compact('name', 'value', 'labels'));
        } catch (RPC\Exception\ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function sub(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Sub', compact('name', 'value', 'labels'));
        } catch (RPC\Exception\ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function observe(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Observe', compact('name', 'value', 'labels'));
        } catch (RPC\Exception\ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, float $value, array $labels = []): void
    {
        try {
            $this->rpc->call('Set', compact('name', 'value', 'labels'));
        } catch (RPC\Exception\ServiceException $e) {
            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $name
     * @param Collector $collector
     */
    public function declare(string $name, Collector $collector): void
    {
        try {
            $this->rpc->call(
                'Declare',
                [
                    'name' => $name,
                    'collector' => $collector->jsonSerialize()
                ]
            );
        } catch (RPC\Exception\ServiceException $e) {
            if (strpos($e->getMessage(), 'tried to register existing collector')) {
                // suppress duplicate metric error
                return;
            }

            throw new MetricsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
