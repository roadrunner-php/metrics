<?php

namespace Spiral\RoadRunner\Metrics;

use Spiral\Goridge\RPC\RPCInterface;

class MetricsFactory
{
    public function create(RPCInterface $rpc, MetricsOptions $options): MetricsInterface
    {
        return new RetryMetrics(
            new Metrics($rpc),
            $options->retryAttempts,
            $options->retrySleepMicroseconds,
        );
    }
}
