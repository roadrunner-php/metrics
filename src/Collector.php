<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use JetBrains\PhpStorm\Pure;

/**
 * @psalm-import-type ArrayFormatType from CollectorInterface
 */
final class Collector implements CollectorInterface, \JsonSerializable
{
    private string $namespace = '';
    private string $subsystem = '';
    private string $help = '';

    /** @var non-empty-string[] */
    private array $labels = [];

    /** @var float[] */
    private array $buckets = [];

    private function __construct(
        public readonly CollectorType $type,
    ) {}

    /**
     * New histogram metric.
     */
    public static function histogram(float ...$bucket): self
    {
        $self = new self(CollectorType::Histogram);
        /** @psalm-suppress ImpurePropertyAssignment */
        $self->buckets = $bucket;

        return $self;
    }

    /**
     * New gauge metric.
     */
    public static function gauge(): self
    {
        return new self(CollectorType::Gauge);
    }

    /**
     * New counter metric.
     */
    public static function counter(): self
    {
        return new self(CollectorType::Counter);
    }

    /**
     * New summary metric.
     */
    public static function summary(): self
    {
        return new self(CollectorType::Summary);
    }

    #[\Override]
    #[Pure]
    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->namespace = $namespace;

        return $self;
    }

    #[\Override]
    #[Pure]
    public function withSubsystem(string $subsystem): self
    {
        $self = clone $this;
        $self->subsystem = $subsystem;

        return $self;
    }

    #[\Override]
    #[Pure]
    public function withHelp(string $help): self
    {
        $self = clone $this;
        $self->help = $help;

        return $self;
    }

    #[\Override]
    #[Pure]
    public function withLabels(string ...$label): self
    {
        $self = clone $this;
        $self->labels = $label;

        return $self;
    }

    #[\Override]
    #[Pure]
    public function toArray(): array
    {
        return [
            'namespace' => $this->namespace,
            'subsystem' => $this->subsystem,
            'type' => $this->type->value,
            'help' => $this->help,
            'labels' => $this->labels,
            'buckets' => $this->buckets,
        ];
    }

    /**
     * @return ArrayFormatType
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
