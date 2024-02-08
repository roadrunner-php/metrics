<?php

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\MetricsFactory;
use Spiral\RoadRunner\Metrics\MetricsIgnoreResponse;
use Spiral\RoadRunner\Metrics\MetricsOptions;
use Spiral\RoadRunner\Metrics\RetryMetrics;
use Spiral\RoadRunner\Metrics\SuppressExceptionsMetrics;

final class MetricsFactoryTest extends TestCase
{
    /**
     * @dataProvider providerForTestCreate
     */
    public function testCreate(MetricsOptions $options, string $expectedClass, string $rpcInterfaceClass): void
    {
        $factory = new MetricsFactory();

        /** @var MockObject&RPCInterface $rpc */
        $rpc = $this->createMock($rpcInterfaceClass);

        self::assertInstanceOf($expectedClass, $factory->create($rpc, $options));
    }

    /**
     * @dataProvider providerForTestCreate
     */
    public function testCreateStatic(MetricsOptions $options, string $expectedClass, string $rpcInterfaceClass): void
    {
        /** @var MockObject&RPCInterface $rpc */
        $rpc = $this->createMock($rpcInterfaceClass);

        self::assertInstanceOf($expectedClass, MetricsFactory::createMetrics($rpc, $options));
    }

    public function testLogsIfIgnoreResponseButNoAsyncRPCInterface(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')
            ->with('ignoreResponsesWherePossible is true but no AsyncRPCInterface provided');

        $rpc = $this->createMock(RPCInterface::class);

        $factory = new MetricsFactory($logger);
        $factory->create($rpc, new MetricsOptions(ignoreResponsesWherePossible: true));
    }

    public function testLogsIfAsyncRPCInterfaceButNoIgnoreResponses(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')
            ->with('ignoreResponsesWherePossible is false but an AsyncRPCInterface was provided');

        $rpc = $this->createMock(AsyncRPCInterface::class);

        $factory = new MetricsFactory($logger);
        $factory->create($rpc, new MetricsOptions(ignoreResponsesWherePossible: false));
    }

    public static function providerForTestCreate(): array
    {
        return [
            'create RetryMetrics' => [
                'options' => new MetricsOptions(),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class
            ],
            'create SuppressExceptionsMetrics' => [
                'options' => new MetricsOptions(suppressExceptions: true),
                'expectedClass' => SuppressExceptionsMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class
            ],
            'create Metrics if no AsyncRPCInterface' => [
                'options' => new MetricsOptions(ignoreResponsesWherePossible: true),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class
            ],
            'create Metrics if AsyncRPCInterface but ignoreResponse... false' => [
                'options' => new MetricsOptions(ignoreResponsesWherePossible: false),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => RPCInterface::class
            ],
            'create MetricsIgnoreResponse if AsyncRPCInterface' => [
                'options' => new MetricsOptions(retryAttempts: 0, suppressExceptions: false, ignoreResponsesWherePossible: true),
                'expectedClass' => MetricsIgnoreResponse::class,
                'rpcInterfaceClass' => AsyncRPCInterface::class
            ],
            'create MetricsIgnoreResponse with RetryMetrics if AsyncRPCInterface' => [
                'options' => new MetricsOptions(retryAttempts: 3, suppressExceptions: false, ignoreResponsesWherePossible: true),
                'expectedClass' => RetryMetrics::class,
                'rpcInterfaceClass' => AsyncRPCInterface::class
            ],
            'create MetricsIgnoreResponse with SuppressExceptions if AsyncRPCInterface' => [
                'options' => new MetricsOptions(retryAttempts: 3, suppressExceptions: true, ignoreResponsesWherePossible: true),
                'expectedClass' => SuppressExceptionsMetrics::class,
                'rpcInterfaceClass' => AsyncRPCInterface::class
            ],
        ];
    }
}
