<?php

namespace Spiral\RoadRunner\Metrics;

class MetricsOptions
{
    public function __construct(
        public readonly int $retryAttempts = 3,
        public readonly int $retrySleepMicroseconds = 50,
    ) {
    }
}
