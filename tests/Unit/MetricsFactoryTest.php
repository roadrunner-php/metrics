<?php

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use Testo\Test;
use Testo\Data\DataProvider;
use Testo\Assert;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\Metrics;
use Spiral\RoadRunner\Metrics\MetricsFactory;
use Spiral\RoadRunner\Metrics\MetricsIgnoreResponse;
use Spiral\RoadRunner\Metrics\MetricsOptions;
use Spiral\RoadRunner\Metrics\RetryMetrics;
use Spiral\RoadRunner\Metrics\SuppressExceptionsMetrics;

#[Test]
final class MetricsFactoryTest
{
    public static function providerForTestCreate(): array
    {
        return [
            'create RetryMetrics' => [
                'options' => new MetricsOptions(),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class,
            ],
            'create SuppressExceptionsMetrics' => [
                'options' => new MetricsOptions(suppressExceptions: true),
                'expectedClass' => SuppressExceptionsMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class,
            ],
            'create Metrics if no AsyncRPCInterface' => [
                'options' => new MetricsOptions(ignoreResponsesWherePossible: true),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class,
            ],
            'create Metrics if AsyncRPCInterface but ignoreResponse... false' => [
                'options' => new MetricsOptions(ignoreResponsesWherePossible: false),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class,
            ],
            'create MetricsIgnoreResponse if AsyncRPCInterface' => [
                'options' => new MetricsOptions(retryAttempts: 0, suppressExceptions: false, ignoreResponsesWherePossible: true),
                'expectedClass' => MetricsIgnoreResponse::class,
                'rpcInterfaceClass' => AsyncRPCInterface::class,
            ],
            'create MetricsIgnoreResponse with RetryMetrics if AsyncRPCInterface' => [
                'options' => new MetricsOptions(retryAttempts: 3, suppressExceptions: false, ignoreResponsesWherePossible: true),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => AsyncRPCInterface::class,
            ],
            'create MetricsIgnoreResponse with SuppressExceptions if AsyncRPCInterface' => [
                'options' => new MetricsOptions(retryAttempts: 3, suppressExceptions: true, ignoreResponsesWherePossible: true),
                'expectedClass' => SuppressExceptionsMetrics::class,
                'rpcInterfaceClass' => AsyncRPCInterface::class,
            ],
        ];
    }

    #[DataProvider('providerForTestCreate')]
    public function testCreate(MetricsOptions $options, string $expectedClass, string $rpcInterfaceClass): void
    {
        $factory = new MetricsFactory();

        /** @var MockInterface&RPCInterface $rpc */
        $rpc = \Mockery::mock($rpcInterfaceClass)->shouldIgnoreMissing();

        Assert::instanceOf($factory->create($rpc, $options), $expectedClass);
    }

    #[DataProvider('providerForTestCreate')]
    public function testCreateStatic(MetricsOptions $options, string $expectedClass, string $rpcInterfaceClass): void
    {
        /** @var MockInterface&RPCInterface $rpc */
        $rpc = \Mockery::mock($rpcInterfaceClass)->shouldIgnoreMissing();

        Assert::instanceOf(MetricsFactory::createMetrics($rpc, $options), $expectedClass);
    }

    public function testLogsIfIgnoreResponseButNoAsyncRPCInterface(): void
    {
        $logger = \Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $logger->shouldReceive('warning')->once()->with('ignoreResponsesWherePossible is true but no AsyncRPCInterface provided', \Mockery::andAnyOtherArgs());

        $rpc = \Mockery::mock(RPCInterface::class)->shouldIgnoreMissing();

        $factory = new MetricsFactory($logger);
        $factory->create($rpc, new MetricsOptions(ignoreResponsesWherePossible: true));
    }

    public function testLogsIfAsyncRPCInterfaceButNoIgnoreResponses(): void
    {
        $logger = \Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $logger->shouldReceive('warning')->once()->with('ignoreResponsesWherePossible is false but an AsyncRPCInterface was provided', \Mockery::andAnyOtherArgs());

        $rpc = \Mockery::mock(AsyncRPCInterface::class)->shouldIgnoreMissing();

        $factory = new MetricsFactory($logger);
        $factory->create($rpc, new MetricsOptions(ignoreResponsesWherePossible: false));
    }

    public function testSuppressedMetricsLogFailureAfterRetries(): void
    {
        $logger = \Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')->once()->with('[Metrics] Operation "Add" was failed: counter cannot decrease in value');

        $rpc = \Mockery::mock(RPCInterface::class);
        $rpc->shouldReceive('withServicePrefix')->with('metrics')->andReturnSelf();
        $rpc->shouldReceive('call')->times(3)->andThrow(new ServiceException('counter cannot decrease in value'));

        $metrics = (new MetricsFactory($logger))->create(
            $rpc,
            new MetricsOptions(retryAttempts: 2, retrySleepMicroseconds: 0, suppressExceptions: true),
        );

        $metrics->add('counter', -1);
    }

    public function testCreatesPlainMetricsWithoutDecorators(): void
    {
        $rpc = \Mockery::mock(RPCInterface::class)->shouldIgnoreMissing();

        $metrics = MetricsFactory::createMetrics($rpc, new MetricsOptions(retryAttempts: 0));

        Assert::same($metrics::class, Metrics::class);
    }
}
