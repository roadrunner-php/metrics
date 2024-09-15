<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\RPCInterface;

abstract class AbstractMetrics implements MetricsInterface
{
    protected readonly RPCInterface $rpc;

	/**
	 * @var non-empty-string
	 */
    protected const SERVICE_NAME = 'metrics';

    public function __construct(RPCInterface $rpc)
    {
        $this->rpc = $rpc->withServicePrefix(static::SERVICE_NAME);
    }
}
