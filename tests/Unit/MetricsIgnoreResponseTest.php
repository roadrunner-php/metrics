<?php

declare(strict_types=1);


use PHPUnit\Framework\TestCase;
use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\CollectorInterface;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\Metrics;
use Spiral\RoadRunner\Metrics\MetricsIgnoreResponse;

final class MetricsIgnoreResponseTest extends TestCase
{
    private MetricsIgnoreResponse $metrics;
    private \PHPUnit\Framework\MockObject\MockObject&AsyncRPCInterface $rpc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rpc = $this->createMock(AsyncRPCInterface::class);
        $this->metrics = new MetricsIgnoreResponse($this->rpc);
    }

    public function testAdd(): void
    {
        $this->rpc->expects($this->once())
            ->method('callIgnoreResponse')
            ->with('metrics.Add', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']]);

        $this->metrics->add('foo', 1.0, ['bar', 'baz']);
    }

    public function testSub(): void
    {
        $this->rpc->expects($this->once())
            ->method('callIgnoreResponse')
            ->with('metrics.Sub', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']]);

        $this->metrics->sub('foo', 1.0, ['bar', 'baz']);
    }

    public function testObserve(): void
    {
        $this->rpc->expects($this->once())
            ->method('callIgnoreResponse')
            ->with('metrics.Observe', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']]);

        $this->metrics->observe('foo', 1.0, ['bar', 'baz']);
    }

    public function testSet(): void
    {
        $this->rpc->expects($this->once())
            ->method('callIgnoreResponse')
            ->with('metrics.Set', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']]);

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
            ->with('metrics.Declare', ['name' => 'foo', 'collector' => $payload])
            ->willReturn(null);

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
            ->with('metrics.Unregister', 'foo')
            ->willReturn(null);

        $this->metrics->unregister('foo');
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
