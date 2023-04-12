<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Metrics;

use JetBrains\PhpStorm\Pure;

/**
 * @psalm-type ArrayFormatType = array{
 *      type:       non-empty-string,
 *      namespace:  string,
 *      subsystem:  string,
 *      help:       string,
 *      labels:     array<array-key, non-empty-string>,
 *      buckets:    array<array-key, float>
 * }
 */
interface CollectorInterface
{
    /**
     * @param non-empty-string $namespace
     */
    #[Pure]
    public function withNamespace(string $namespace): self;

    /**
     * @param non-empty-string $subsystem
     */
    #[Pure]
    public function withSubsystem(string $subsystem): self;

    /**
     * @param non-empty-string $help
     */
    #[Pure]
    public function withHelp(string $help): self;

    /**
     * @param non-empty-string ...$label
     */
    #[Pure]
    public function withLabels(string ...$label): self;

    /**
     * @return ArrayFormatType
     */
    #[Pure]
    public function toArray(): array;
}
