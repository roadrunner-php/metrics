<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

enum CollectorType: string
{
    case Histogram = 'histogram';
    case Gauge = 'gauge';
    case Counter = 'counter';
    case Summary = 'summary';
}
