<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics\Tests\Unit;

use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\CollectorType;
use Testo\Assert;
use Testo\Test;

#[Test]
final class CollectorTest
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

        Assert::same($collector->toArray(), $expected);
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

        Assert::same($collector->jsonSerialize(), $expected);
    }

    public function testWithersKeepOriginalUnchanged(): void
    {
        $collector = Collector::summary();

        $collector->withNamespace('test')->withSubsystem('subsystem')->withHelp('help')->withLabels('foo');

        Assert::same($collector->toArray(), [
            'namespace' => '',
            'subsystem' => '',
            'type' => 'summary',
            'help' => '',
            'labels' => [],
            'buckets' => [],
        ]);
    }

    public function testWithLabelsReplacesLabels(): void
    {
        $collector = Collector::counter()->withLabels('foo', 'bar')->withLabels('baz');

        Assert::same($collector->toArray()['labels'], ['baz']);
    }

    public function testJsonEncode(): void
    {
        $collector = Collector::histogram(0.5, 1.5)->withNamespace('app')->withLabels('route');

        Assert::same(
            \json_encode($collector),
            '{"namespace":"app","subsystem":"","type":"histogram","help":"","labels":["route"],"buckets":[0.5,1.5]}',
        );
    }

    public function testHistogram(): void
    {
        $collector = Collector::histogram(1.0, 2.0, 3.0);

        Assert::same($collector->type, CollectorType::Histogram);
        Assert::same($collector->toArray()['buckets'], [1.0, 2.0, 3.0]);
    }

    public function testGauge(): void
    {
        $collector = Collector::gauge();

        Assert::same($collector->type, CollectorType::Gauge);
        Assert::same($collector->toArray()['buckets'], []);
    }

    public function testCounter(): void
    {
        $collector = Collector::counter();

        Assert::same($collector->type, CollectorType::Counter);
        Assert::same($collector->toArray()['buckets'], []);
    }

    public function testSummary(): void
    {
        $collector = Collector::summary();

        Assert::same($collector->type, CollectorType::Summary);
        Assert::same($collector->toArray()['buckets'], []);
    }

    public function testWithNamespace(): void
    {
        $collector = Collector::counter();

        $newCollector = $collector->withNamespace('test');
        Assert::notSame($newCollector, $collector);
        Assert::same($newCollector->toArray()['namespace'], 'test');
    }

    public function testWithSubsystem(): void
    {
        $collector = Collector::gauge();

        $newCollector = $collector->withSubsystem('subsystem');
        Assert::notSame($newCollector, $collector);
        Assert::same($newCollector->toArray()['subsystem'], 'subsystem');
    }

    public function testWithHelp(): void
    {
        $collector = Collector::histogram(1.0, 2.0, 3.0);

        $newCollector = $collector->withHelp('help');
        Assert::notSame($newCollector, $collector);
        Assert::same($newCollector->toArray()['help'], 'help');
    }

    public function testWithLabels(): void
    {
        $collector = Collector::counter();

        $newCollector = $collector->withLabels('foo', 'bar');
        Assert::notSame($newCollector, $collector);
        Assert::same($newCollector->toArray()['labels'], ['foo', 'bar']);
    }
}
