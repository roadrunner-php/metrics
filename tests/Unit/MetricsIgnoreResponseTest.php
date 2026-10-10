<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use Mockery\MockInterface;
use Spiral\Goridge\RPC\AsyncRPCInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\RoadRunner\Metrics\CollectorInterface;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\MetricsIgnoreResponse;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class MetricsIgnoreResponseTest
{
    private MetricsIgnoreResponse $metrics;
    private MockInterface&AsyncRPCInterface $rpc;

    public function testAdd(): void
    {
        $this->rpc->shouldReceive('callIgnoreResponse')->once()->with('Add', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs());

        $this->metrics->add('foo', 1.0, ['bar', 'baz']);
    }

    public function testSub(): void
    {
        $this->rpc->shouldReceive('callIgnoreResponse')->once()->with('Sub', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs());

        $this->metrics->sub('foo', 1.0, ['bar', 'baz']);
    }

    public function testObserve(): void
    {
        $this->rpc->shouldReceive('callIgnoreResponse')->once()->with('Observe', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs());

        $this->metrics->observe('foo', 1.0, ['bar', 'baz']);
    }

    public function testSet(): void
    {
        $this->rpc->shouldReceive('callIgnoreResponse')->once()->with('Set', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs());

        $this->metrics->set('foo', 1.0, ['bar', 'baz']);
    }

    #[DataSet(['add', 'Add'], 'add')]
    #[DataSet(['sub', 'Sub'], 'sub')]
    #[DataSet(['observe', 'Observe'], 'observe')]
    #[DataSet(['set', 'Set'], 'set')]
    public function testCallIgnoreResponseWithError(string $method, string $rpcMethod): void
    {
        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)
            ->withMessage($e->getMessage())
            ->withCode($e->getCode())
            ->withPrevious($e);

        $this->rpc->shouldReceive('callIgnoreResponse')->once()->with($rpcMethod, \Mockery::andAnyOtherArgs())->andThrow($e);

        $this->metrics->$method('foo', 1.0, ['bar', 'baz']);
    }

    public function testDeclare(): void
    {
        $collector = \Mockery::mock(CollectorInterface::class)->shouldIgnoreMissing();
        $collector->shouldReceive('toArray')->once()->andReturn($payload = ['foo' => 'bar']);

        $this->rpc->shouldReceive('call')->once()->with('Declare', ['name' => 'foo', 'collector' => $payload], \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->declare('foo', $collector);
    }

    public function testDeclareWithError(): void
    {
        $collector = \Mockery::mock(CollectorInterface::class)->shouldIgnoreMissing();
        $collector->shouldReceive('toArray')->andReturn(['foo' => 'bar']);

        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)->withMessageContaining($e->getMessage())->withCode($e->getCode());

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->declare('foo', $collector);
    }

    public function testDeclareWithSuppressedError(): void
    {
        $collector = \Mockery::mock(CollectorInterface::class)->shouldIgnoreMissing();
        $collector->shouldReceive('toArray')->andReturn(['foo' => 'bar']);

        $e = new ServiceException('Something tried to register existing collector.', 1);

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->declare('foo', $collector);
    }

    public function testUnregister(): void
    {
        $this->rpc->shouldReceive('call')->once()->with('Unregister', 'foo', \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->unregister('foo');
    }

    public function testUnregisterWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)->withMessageContaining($e->getMessage())->withCode($e->getCode());

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->unregister('foo');
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->rpc = \Mockery::mock(AsyncRPCInterface::class)->shouldIgnoreMissing();
        $this->rpc->shouldReceive('withServicePrefix')->once()->with('metrics', \Mockery::andAnyOtherArgs())->andReturn($this->rpc);

        $this->metrics = new MetricsIgnoreResponse($this->rpc);
    }
}
