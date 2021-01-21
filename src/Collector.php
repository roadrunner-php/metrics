<?php

/**
 * High-performance PHP process supervisor and load balancer written in Go. Http core.
 */

namespace Spiral\RoadRunner\Metrics;

final class Collector implements \JsonSerializable
{
    public const HISTOGRAM = 'histogram';
    public const GAUGE = 'gauge';
    public const COUNTER = 'counter';

    private string $namespace = '';
    private string $subsystem = '';
    private string $type;
    private string $help = '';
    private array $labels = [];
    private array $buckets = [];

    /**
     * @param string $type
     */
    private function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function withNamespace(string $namespace): self
    {
        $c = clone $this;
        $c->namespace = $namespace;

        return $c;
    }

    /**
     * @param string $subsystem
     * @return $this
     */
    public function withSubsystem(string $subsystem): self
    {
        $c = clone $this;
        $c->subsystem = $subsystem;

        return $c;
    }

    /**
     * @param string $help
     * @return $this
     */
    public function withHelp(string $help): self
    {
        $c = clone $this;
        $c->help = $help;

        return $c;
    }

    /**
     * @param string ...$label
     * @return $this
     */
    public function withLabels(string ...$label): self
    {
        $c = clone $this;
        $c->labels = $label;

        return $c;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'namespace' => $this->namespace,
            'subsystem' => $this->subsystem,
            'type' => $this->type,
            'help' => $this->help,
            'labels' => $this->labels,
            'buckets' => $this->buckets,
        ];
    }

    /**
     * New histogram metric.
     *
     * @param float ...$bucket
     * @return static
     */
    public static function histogram(float ...$bucket): self
    {
        $c = new self(self::HISTOGRAM);
        $c->buckets = $bucket;

        return $c;
    }

    /**
     * New gauge metric.
     *
     * @return static
     */
    public static function gauge(): self
    {
        $c = new self(self::GAUGE);

        return $c;
    }

    /**
     * New counter metric.
     *
     * @return static
     */
    public static function counter(): self
    {
        $c = new self(self::COUNTER);

        return $c;
    }
}