<?php

namespace Spiral\RoadRunner\Metrics;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\RPCInterface;

class MetricsFactory
{
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function create(RPCInterface $rpc, MetricsOptions $options = new MetricsOptions()): MetricsInterface
    {
        if ($options->ignoreResponsesWherePossible && !($rpc instanceof AsyncRPCInterface)) {
            $this->logger->warning("ignoreResponsesWherePossible is true but no AsyncRPCInterface provided");
        } elseif (!$options->ignoreResponsesWherePossible && $rpc instanceof AsyncRPCInterface) {
            $this->logger->warning("ignoreResponsesWherePossible is false but an AsyncRPCInterface was provided");
        }

        if ($options->ignoreResponsesWherePossible && $rpc instanceof AsyncRPCInterface) {
            $metrics = new MetricsIgnoreResponse($rpc);
        } else {
            $metrics = new Metrics($rpc);
        }

        if ($options->retryAttempts > 0) {
            $metrics = new RetryMetrics(
                $metrics,
                $options->retryAttempts,
                $options->retrySleepMicroseconds,
            );
        }

        if ($options->suppressExceptions) {
            $metrics = new SuppressExceptionsMetrics($metrics, $this->logger);
        }

        return $metrics;
    }

    public static function createMetrics(
        RPCInterface    $rpc,
        MetricsOptions  $options = new MetricsOptions(),
        LoggerInterface $logger = new NullLogger()
    ): MetricsInterface
    {
        return (new self($logger))->create($rpc, $options);
    }
}
