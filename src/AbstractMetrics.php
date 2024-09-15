<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\RPCInterface;

abstract class AbstractMetrics implements MetricsInterface
{
	/**
	 * @var string
	 */
    protected const SERVICE_NAME = 'metrics';
}
