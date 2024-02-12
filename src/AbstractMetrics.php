<?php

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\RPCInterface;

abstract class AbstractMetrics implements MetricsInterface
{
    protected const SERVICE_NAME = 'metrics';
}
