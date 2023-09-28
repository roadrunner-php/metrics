<?php

namespace Spiral\RoadRunner\Metrics;

class MetricsOptions
{
    /**
     * @param int<0, max> $retryAttempts
     * @param int<0, max> $retrySleepMicroseconds
     */
    public function __construct(
        public readonly int $retryAttempts = 3,
        public readonly int $retrySleepMicroseconds = 50,
        public readonly bool $suppressExceptions = false,
    ) {
    }
}
