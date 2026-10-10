<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\MetricsInterface;
use Spiral\RoadRunner\Metrics\RetryMetrics;
use Testo\Expect;
use Testo\Test;

#[Test]
final class RetryMetricsTest
{
    public function testAddWithMetricsException(): void
    {
        $metrics = $this->createMetricsMock('add', 4, 4);

        $retryMetrics = new RetryMetrics(
            $metrics,
            3,
            1,
        );

        Expect::exception(MetricsException::class);

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

        Expect::exception(MetricsException::class);

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

        Expect::exception(MetricsException::class);

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

        Expect::exception(MetricsException::class);

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

        Expect::exception(MetricsException::class);

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

        Expect::exception(MetricsException::class);

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

    public function testCallsOnceOnSuccess(): void
    {
        $metrics = \Mockery::mock(MetricsInterface::class);
        $metrics->shouldReceive('add')->once()->with('counter', 1.0, ['label']);

        $retryMetrics = new RetryMetrics($metrics, 3, 0);

        $retryMetrics->add('counter', 1, ['label']);
    }

    public function testZeroRetryAttemptsCallsOnce(): void
    {
        $metrics = $this->createMetricsMock('add', 1, 1);

        $retryMetrics = new RetryMetrics($metrics, 0, 0);

        Expect::exception(MetricsException::class);

        $retryMetrics->add('counter', 1);
    }

    public function testRethrowsLastMetricsException(): void
    {
        $metrics = \Mockery::mock(MetricsInterface::class);
        $metrics->shouldReceive('add')->twice()->andThrowExceptions([
            new MetricsException('first'),
            new MetricsException('last'),
        ]);

        $retryMetrics = new RetryMetrics($metrics, 1, 0);

        Expect::exception(MetricsException::class)->withMessage('last');

        $retryMetrics->add('counter', 1);
    }

    public function testDoesNotRetryOtherExceptions(): void
    {
        $metrics = \Mockery::mock(MetricsInterface::class);
        $metrics->shouldReceive('add')->once()->andThrow(new \RuntimeException('unexpected'));

        $retryMetrics = new RetryMetrics($metrics, 3, 0);

        Expect::exception(\RuntimeException::class)->withMessage('unexpected');

        $retryMetrics->add('counter', 1);
    }

    private function createMetricsMock(string $method, int $expectedCalls, int $exceptions): MetricsInterface
    {
        $metrics = \Mockery::mock(MetricsInterface::class)->shouldIgnoreMissing();

        $returnValues = \array_fill(0, $exceptions, static fn() => throw new MetricsException());
        $returnValues[] = static fn() => null;

        $metrics->shouldReceive($method)->times($expectedCalls)->andReturnUsing(...$returnValues);

        return $metrics;
    }
}
