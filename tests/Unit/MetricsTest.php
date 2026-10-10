<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use Mockery\MockInterface;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\CollectorInterface;
use Spiral\RoadRunner\Metrics\Exception\MetricsException;
use Spiral\RoadRunner\Metrics\Metrics;
use Testo\Expect;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class MetricsTest
{
    private Metrics $metrics;
    private MockInterface|RPCInterface $rpc;

    public function testAdd(): void
    {
        $this->rpc->shouldReceive('call')->once()->with('Add', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->add('foo', 1.0, ['bar', 'baz']);
    }

    public function testAddWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)->withMessageContaining($e->getMessage())->withCode($e->getCode());

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->add('foo', 1.0, ['bar', 'baz']);
    }

    public function testAddNegativeValueToCounterIsRejected(): void
    {
        $e = new ServiceException('counter cannot decrease in value', 1);

        Expect::exception(MetricsException::class)
            ->withMessage('counter cannot decrease in value')
            ->withPrevious($e);

        $this->rpc->shouldReceive('call')->once()->with('Add', ['name' => 'foo', 'value' => -1.0, 'labels' => []], \Mockery::andAnyOtherArgs())->andThrow($e);

        $this->metrics->add('foo', -1.0);
    }

    public function testSub(): void
    {
        $this->rpc->shouldReceive('call')->once()->with('Sub', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->sub('foo', 1.0, ['bar', 'baz']);
    }

    public function testSubWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)->withMessageContaining($e->getMessage())->withCode($e->getCode());

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->sub('foo', 1.0, ['bar', 'baz']);
    }

    public function testObserve(): void
    {
        $this->rpc->shouldReceive('call')->once()->with('Observe', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->observe('foo', 1.0, ['bar', 'baz']);
    }

    public function testObserveWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)->withMessageContaining($e->getMessage())->withCode($e->getCode());

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->observe('foo', 1.0, ['bar', 'baz']);
    }

    public function testSet(): void
    {
        $this->rpc->shouldReceive('call')->once()->with('Set', ['name' => 'foo', 'value' => 1.0, 'labels' => ['bar', 'baz']], \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->set('foo', 1.0, ['bar', 'baz']);
    }

    public function testSetWithError(): void
    {
        $e = new ServiceException('Something went wrong', 1);

        Expect::exception(MetricsException::class)->withMessageContaining($e->getMessage())->withCode($e->getCode());

        $this->rpc->shouldReceive('call')->once()->andThrow($e);

        $this->metrics->set('foo', 1.0, ['bar', 'baz']);
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

    public function testDeclarePassesCollectorDefinition(): void
    {
        $collector = Collector::histogram(0.1, 0.5)->withNamespace('app')->withHelp('Request duration')->withLabels('route');

        $this->rpc->shouldReceive('call')->once()->with('Declare', [
            'name' => 'duration',
            'collector' => [
                'namespace' => 'app',
                'subsystem' => '',
                'type' => 'histogram',
                'help' => 'Request duration',
                'labels' => ['route'],
                'buckets' => [0.1, 0.5],
            ],
        ], \Mockery::andAnyOtherArgs())->andReturn(null);

        $this->metrics->declare('duration', $collector);
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
        $this->rpc = \Mockery::mock(RPCInterface::class)->shouldIgnoreMissing();
        $this->rpc->shouldReceive('withServicePrefix')->once()->with('metrics', \Mockery::andAnyOtherArgs())->andReturn($this->rpc);

        $this->metrics = new Metrics($this->rpc);
    }
}
