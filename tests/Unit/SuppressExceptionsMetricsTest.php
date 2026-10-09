<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\MetricsInterface;
use Spiral\RoadRunner\Metrics\SuppressExceptionsMetrics;
use Testo\Assert\ExpectNoAssertions;
use Testo\Data\DataProvider;
use Testo\Expect;
use Testo\Test;

#[Test]
final class SuppressExceptionsMetricsTest
{
    public static function providerOperations(): iterable
    {
        yield 'add' => ['add', ['foo', 1.0, ['bar']], 'Add'];
        yield 'sub' => ['sub', ['foo', 1.0, ['bar']], 'Sub'];
        yield 'observe' => ['observe', ['foo', 1.0, ['bar']], 'Observe'];
        yield 'set' => ['set', ['foo', 1.0, ['bar']], 'Set'];
        yield 'declare' => ['declare', ['foo', Collector::counter()], 'Declare'];
        yield 'unregister' => ['unregister', ['foo'], 'Unregister'];
    }

    #[DataProvider('providerOperations')]
    public function testDelegatesToWrappedMetrics(string $method, array $arguments, string $operation): void
    {
        /** @var MockInterface&MetricsInterface $metrics */
        $metrics = \Mockery::mock(MetricsInterface::class);
        $metrics->expects($method)->with(...$arguments);

        /** @var MockInterface&LoggerInterface $logger */
        $logger = \Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('warning');

        (new SuppressExceptionsMetrics($metrics, $logger))->$method(...$arguments);
    }

    #[DataProvider('providerOperations')]
    public function testLogsMetricsExceptionInsteadOfThrowing(string $method, array $arguments, string $operation): void
    {
        /** @var MockInterface&MetricsInterface $metrics */
        $metrics = \Mockery::mock(MetricsInterface::class);
        $metrics->expects($method)->andThrow(new MetricsException('counter cannot decrease in value'));

        /** @var MockInterface&LoggerInterface $logger */
        $logger = \Mockery::mock(LoggerInterface::class);
        $logger->expects('warning')
            ->with(\sprintf('[Metrics] Operation "%s" was failed: counter cannot decrease in value', $operation));

        (new SuppressExceptionsMetrics($metrics, $logger))->$method(...$arguments);
    }

    #[DataProvider('providerOperations')]
    #[ExpectNoAssertions]
    public function testSuppressesMetricsExceptionWithDefaultLogger(string $method, array $arguments, string $operation): void
    {
        $metrics = \Mockery::spy(MetricsInterface::class);
        $metrics->allows($method)->andThrow(new MetricsException('error'));

        (new SuppressExceptionsMetrics($metrics))->$method(...$arguments);
    }

    #[DataProvider('providerOperations')]
    public function testDoesNotSuppressOtherExceptions(string $method, array $arguments, string $operation): void
    {
        /** @var MockInterface&MetricsInterface $metrics */
        $metrics = \Mockery::mock(MetricsInterface::class);
        $metrics->allows($method)->andThrow(new \RuntimeException('unexpected'));

        Expect::exception(\RuntimeException::class)->withMessage('unexpected');

        (new SuppressExceptionsMetrics($metrics, \Mockery::spy(LoggerInterface::class)))->$method(...$arguments);
    }
}
