<?php

namespace Spiral\RoadRunner\Metrics;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Goridge\RPC\RPCInterface;

class MetricsFactory
{
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function create(RPCInterface $rpc, MetricsOptions $options): MetricsInterface
    {
        $metrics = new RetryMetrics(
            new Metrics($rpc),
            $options->retryAttempts,
            $options->retrySleepMicroseconds,
        );

        if ($options->suppressExceptions) {
            $metrics = new SuppressExceptionsMetrics($metrics, $this->logger);
        }

        return $metrics;
    }
}
