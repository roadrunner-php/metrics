<?php

namespace Spiral\RoadRunner\Metrics;

use JetBrains\PhpStorm\Pure;
use JsonSerializable;

/**
 * @psalm-import-type ArrayFormatType from CollectorInterface
 */
final class Collector implements CollectorInterface, JsonSerializable
{
    /** @var string */
    private string $namespace = '';

    /** @var string */
    private string $subsystem = '';

    /** @var string */
    private string $help = '';

    /** @var non-empty-string[] */
    private array $labels = [];

    /** @var float[] */
    private array $buckets = [];

    private function __construct(
        public readonly CollectorType $type,
    ) {
    }

    #[Pure]
    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->namespace = $namespace;

        return $self;
    }

    #[Pure]
    public function withSubsystem(string $subsystem): self
    {
        $self = clone $this;
        $self->subsystem = $subsystem;

        return $self;
    }

    #[Pure]
    public function withHelp(string $help): self
    {
        $self = clone $this;
        $self->help = $help;

        return $self;
    }

    #[Pure]
    public function withLabels(string ...$label): self
    {
        $self = clone $this;
        $self->labels = $label;

        return $self;
    }

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
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * New histogram metric.
     *
     * @param float ...$bucket
     * @return static
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
     *
     * @return static
     */
    public static function gauge(): self
    {
        return new self(CollectorType::Gauge);
    }

    /**
     * New counter metric.
     *
     * @return static
     */
    public static function counter(): self
    {
        return new self(CollectorType::Counter);
    }
}
