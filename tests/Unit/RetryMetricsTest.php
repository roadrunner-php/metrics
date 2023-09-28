<?php

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\MetricsInterface;
use Spiral\RoadRunner\Metrics\RetryMetrics;

final class RetryMetricsTest extends TestCase
{
    public function testAddWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('add', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        self::expectException(MetricsException::class);

        $retryMetrics->add('counter', 1);
    }

    public function testAddOk(): void
    {
        $metrics = $this->createMetricsMock('add', 4, 3);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        $retryMetrics->add('counter', 1);
    }

    public function testSubWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('sub', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        self::expectException(MetricsException::class);

        $retryMetrics->sub('counter', 1);
    }

    public function testSubOk(): void
    {
        $metrics = $this->createMetricsMock('sub', 4, 3);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        $retryMetrics->sub('counter', 1);
    }

    public function testObserveWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('observe', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        self::expectException(MetricsException::class);

        $retryMetrics->observe('counter', 1);
    }

    public function testObserveOk(): void
    {
        $metrics = $this->createMetricsMock('observe', 4, 3);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        $retryMetrics->observe('counter', 1);
    }

    public function testSetWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('set', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        self::expectException(MetricsException::class);

        $retryMetrics->set('counter', 1);
    }

    public function testSetOk(): void
    {
        $metrics = $this->createMetricsMock('set', 4, 3);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        $retryMetrics->set('counter', 1);
    }

    public function testDeclareWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('declare', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        self::expectException(MetricsException::class);

        $retryMetrics->declare('counter', Collector::counter());
    }

    public function testDeclareOk(): void
    {
        $metrics = $this->createMetricsMock('declare', 4, 3);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        $retryMetrics->declare('counter', Collector::counter());
    }

    public function testUnregisterWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('unregister', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        self::expectException(MetricsException::class);

        $retryMetrics->unregister('counter');
    }

    public function testUnregisterOk(): void
    {
        $metrics = $this->createMetricsMock('unregister', 4, 3);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        $retryMetrics->unregister('counter');
    }

    private function createMetricsMock(string $method, int $expectedCalls, int $exceptions): MetricsInterface
    {
        $metrics = $this->createMock(MetricsInterface::class);

        $metrics
            ->expects(new InvokedCount($expectedCalls))
            ->method($method)
            ->willReturnOnConsecutiveCalls(...array_fill(
                0,
                $exceptions,
                $this->throwException(new MetricsException()),
            ));

        return $metrics;
    }
}
