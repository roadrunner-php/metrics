<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\CollectorInterface;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\Metrics;

final class MetricsTest extends TestCase
{
    private Metrics $metrics;
    private MockObject|RPCInterface $rpc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rpc = $this->createMock(RPCInterface::class);
        $this->rpc->expects($this->once())->method('withServicePrefix')
            ->with('metrics')
            ->willReturn($this->rpc);

        $this->metrics = new Metrics($this->rpc);
    }

    public static function metricsProvider(): iterable
    {
        yield [
            [
                'name' => 'foo',
                'value' => 1.0,
            ],
            ['name' => 'foo', 'value' => 1.0, 'labels' => [], 'namespace' => ''],
        ];

        yield [
            [
                'name' => 'foo',
                'value' => 1.0,
                'labels' => ['bar', 'baz'],
            ],
            ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz'], 'namespace' => ''],
        ];

        yield [
            [
                'name' => 'foo',
                'value' => 1.0,
                'namespace' => 'ns1',
            ],
            ['name' => 'foo', 'value' => 1.0, 'labels' => [], 'namespace' => 'ns1'],
        ];

        yield [
            [
                'name' => 'foo',
                'value' => 1.0,
                'labels' => ['bar', 'baz'],
                'namespace' => 'ns1',
            ],
            ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz'], 'namespace' => 'ns1'],
        ];
    }

    #[DataProvider('metricsProvider')]
    public function testAdd(array $params, array $expected): void
    {
        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Add', $expected);

        $this->metrics->add(...$params);
    }

    public function testAddWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        $this->expectException(MetricsException::class);
        $this->expectExceptionMessage($e->getMessage());
        $this->expectExceptionCode($e->getCode());

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->add(name: 'foo', value: 1.0);
    }

    #[DataProvider('metricsProvider')]
    public function testSub(array $params, array $expected): void
    {
        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Sub', $expected);

        $this->metrics->sub(...$params);
    }

    public function testSubWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        $this->expectException(MetricsException::class);
        $this->expectExceptionMessage($e->getMessage());
        $this->expectExceptionCode($e->getCode());

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->sub('foo', 1.0, ['bar', 'baz']);
    }

    #[DataProvider('metricsProvider')]
    public function testObserve(array $params, array $expected): void
    {
        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Observe', $expected);

        $this->metrics->observe(...$params);
    }

    public function testObserveWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        $this->expectException(MetricsException::class);
        $this->expectExceptionMessage($e->getMessage());
        $this->expectExceptionCode($e->getCode());

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->observe('foo', 1.0, ['bar', 'baz']);
    }

    #[DataProvider('metricsProvider')]
    public function testSet(array $params, array $expected): void
    {
        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Set', $expected);

        $this->metrics->set(...$params);
    }

    public function testSetWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        $this->expectException(MetricsException::class);
        $this->expectExceptionMessage($e->getMessage());
        $this->expectExceptionCode($e->getCode());

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->set('foo', 1.0, ['bar', 'baz']);
    }

    public function testDeclare(): void
    {
        $collector = $this->createMock(CollectorInterface::class);
        $collector->expects($this->once())
            ->method('toArray')
            ->willReturn($payload = ['foo' => 'bar']);

        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Declare', ['name' => 'foo', 'collector' => $payload]);

        $this->metrics->declare('foo', $collector);
    }

    public function testDeclareWithError(): void
    {
        $collector = $this->createMock(CollectorInterface::class);
        $collector->method('toArray')->willReturn(['foo' => 'bar']);

        $e = new ServiceException('Something went wrong', 1);

        $this->expectException(MetricsException::class);
        $this->expectExceptionMessage($e->getMessage());
        $this->expectExceptionCode($e->getCode());

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->declare('foo', $collector);
    }

    public function testDeclareWithSuppressedError(): void
    {
        $collector = $this->createMock(CollectorInterface::class);
        $collector->method('toArray')->willReturn(['foo' => 'bar']);

        $e = new ServiceException('Something tried to register existing collector.', 1);

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->declare('foo', $collector);
    }

    public function testUnregister(): void
    {
        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Unregister', 'foo');

        $this->metrics->unregister('foo');
    }

    public function testUnregisterWithNamespace(): void
    {
        $this->rpc->expects($this->once())
            ->method('call')
            ->with('Unregister', 'foo@ns1');

        $this->metrics->unregister(name: 'foo', namespace: 'ns1');
    }

    public function testUnregisterWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        $this->expectException(MetricsException::class);
        $this->expectExceptionMessage($e->getMessage());
        $this->expectExceptionCode($e->getCode());

        $this->rpc->expects($this->once())
            ->method('call')
            ->willThrowException($e);

        $this->metrics->unregister('foo');
    }
}
