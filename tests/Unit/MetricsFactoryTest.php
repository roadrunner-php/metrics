<?php

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\Goridge\RPC\RPC;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\MetricsFactory;
use Spiral\RoadRunner\Metrics\MetricsOptions;
use Spiral\RoadRunner\Metrics\RetryMetrics;
use Spiral\RoadRunner\Metrics\SuppressExceptionsMetrics;

final class MetricsFactoryTest extends TestCase
{
    /**
     * @dataProvider providerForTestCreate
     */
    public function testCreate(MetricsOptions $options, string $expectedClass): void
    {
        $factory = new MetricsFactory();

        $rpc = $this->createMock(RPCInterface::class);
        $rpc->expects($this->once())->method('withServicePrefix')
            ->with('metrics')
            ->willReturn($rpc);

        self::assertInstanceOf($expectedClass, $factory->create($rpc, $options));
    }

    public static function providerForTestCreate(): array
    {
        return [
            'create RetryMetrics' => [
                'options' => new MetricsOptions(),
                'expectedClass' => RetryMetrics::class,
            ],
            'create SuppressExceptionsMetrics' => [
                'options' => new MetricsOptions(suppressExceptions: true),
                'expectedClass' => SuppressExceptionsMetrics::class,
            ],
        ];
    }
}
