<?php

/**
 * High-performance PHP process supervisor and load balancer written in Go. Http core.
 */

namespace Spiral\RoadRunner\Metrics\Exception;

use Spiral\Goridge\RPC\Exception\ServiceException;

class MetricsException extends ServiceException
{
}