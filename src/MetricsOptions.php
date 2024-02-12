<?php

namespace Spiral\RoadRunner\Metrics;

class MetricsOptions
{
    /**
     * @param int<0, max> $retryAttempts Number of retry attempts done
     * @param int<0, max> $retrySleepMicroseconds Amount of microSeconds slept between retry attempts
     * @param bool $suppressExceptions Whether to suppress the exceptions usually thrown if something went wrong
     * @param bool $ignoreResponsesWherePossible Whether to use the new callIgnoreResponse RPC interface to speed up Metric collection. May result in lost metrics
     */
    public function __construct(
        public readonly int $retryAttempts = 3,
        public readonly int $retrySleepMicroseconds = 50,
        public readonly bool $suppressExceptions = false,
        public readonly bool $ignoreResponsesWherePossible = false
    ) {
    }
}
