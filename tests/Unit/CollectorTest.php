<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\CollectorType;

final class CollectorTest extends TestCase
{
    public function testToArray(): void
    {
        $collector = Collector::histogram(1.0, 2.0, 3.0)
            ->withNamespace('test')
            ->withSubsystem('subsystem')
            ->withHelp('help')
            ->withLabels('foo', 'bar');

        $expected = [
            'namespace' => 'test',
            'subsystem' => 'subsystem',
            'type' => CollectorType::Histogram->value,
            'help' => 'help',
            'labels' => ['foo', 'bar'],
            'buckets' => [1.0, 2.0, 3.0],
        ];

        $this->assertSame($expected, $collector->toArray());
    }

    public function testJsonSerialize(): void
    {
        $collector = Collector::gauge()
            ->withNamespace('test')
            ->withSubsystem('subsystem')
            ->withHelp('help')
            ->withLabels('foo', 'bar');

        $expected = [
            'namespace' => 'test',
            'subsystem' => 'subsystem',
            'type' => CollectorType::Gauge->value,
            'help' => 'help',
            'labels' => ['foo', 'bar'],
            'buckets' => [],
        ];

        $this->assertSame($expected, $collector->jsonSerialize());
    }

    public function testHistogram(): void
    {
        $collector = Collector::histogram(1.0, 2.0, 3.0);

        $this->assertSame(CollectorType::Histogram, $collector->type);
        $this->assertSame([1.0, 2.0, 3.0], $collector->toArray()['buckets']);
    }

    public function testGauge(): void
    {
        $collector = Collector::gauge();

        $this->assertSame(CollectorType::Gauge, $collector->type);
        $this->assertSame([], $collector->toArray()['buckets']);
    }

    public function testCounter(): void
    {
        $collector = Collector::counter();

        $this->assertSame(CollectorType::Counter, $collector->type);
        $this->assertSame([], $collector->toArray()['buckets']);
    }

    public function testSummary(): void
    {
        $collector = Collector::summary();

        $this->assertSame(CollectorType::Summary, $collector->type);
        $this->assertSame([], $collector->toArray()['buckets']);
    }

    public function testWithNamespace(): void
    {
        $collector = Collector::counter();

        $newCollector = $collector->withNamespace('test');
        $this->assertNotSame($collector, $newCollector);
        $this->assertSame('test', $newCollector->toArray()['namespace']);
    }

    public function testWithSubsystem(): void
    {
        $collector = Collector::gauge();

        $newCollector = $collector->withSubsystem('subsystem');
        $this->assertNotSame($collector, $newCollector);
        $this->assertSame('subsystem', $newCollector->toArray()['subsystem']);
    }

    public function testWithHelp(): void
    {
        $collector = Collector::histogram(1.0, 2.0, 3.0);

        $newCollector = $collector->withHelp('help');
        $this->assertNotSame($collector, $newCollector);
        $this->assertSame('help', $newCollector->toArray()['help']);
    }

    public function testWithLabels(): void
    {
        $collector = Collector::counter();

        $newCollector = $collector->withLabels('foo', 'bar');
        $this->assertNotSame($collector, $newCollector);
        $this->assertSame(['foo', 'bar'], $newCollector->toArray()['labels']);
    }
}
