<p align="center">
    <a href="https://roadrunner.dev"><picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
        <img alt="RoadRunner" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8" style="width: 6in; display: block">
    </picture></a>
</p>

<p align="center">Prometheus metrics for PHP workers via RoadRunner RPC</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://docs.roadrunner.dev/docs/lab/metrics)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/roadrunner-server)

[![Psalm Level](https://shepherd.dev/github/roadrunner-php/metrics/level.svg)](https://shepherd.dev/github/roadrunner-php/metrics)
[![Type Coverage](https://shepherd.dev/github/roadrunner-php/metrics/coverage.svg)](https://shepherd.dev/github/roadrunner-php/metrics)

</div>

<br />

PHP client for the RoadRunner [metrics plugin](https://docs.roadrunner.dev/docs/lab/metrics).
It lets application workers declare Prometheus collectors and publish values to them over RPC.

## Get Started

### Installation

```bash
composer require spiral/roadrunner-metrics
```

[![PHP](https://img.shields.io/packagist/php-v/spiral/roadrunner-metrics.svg?style=flat-square&logo=php)](https://packagist.org/packages/spiral/roadrunner-metrics)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/roadrunner-metrics.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/spiral/roadrunner-metrics)
[![License](https://img.shields.io/packagist/l/spiral/roadrunner-metrics.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/roadrunner-metrics.svg?style=flat-square)](https://packagist.org/packages/spiral/roadrunner-metrics/stats)

The package requires PHP 8.2+ and RoadRunner v3.
You can use the convenient installer to download the latest available compatible version of RoadRunner assembly:

```bash
composer require spiral/roadrunner-cli --dev
vendor/bin/rr get
```

### Configuration

Enable metrics service in your `.rr.yaml` file:

```yaml
rpc:
    listen: tcp://127.0.0.1:6001

server:
    command: "php worker.php"

http:
    address: "0.0.0.0:8080"

metrics:
    address: "0.0.0.0:2112"
```

See the [metrics plugin documentation](https://docs.roadrunner.dev/docs/lab/metrics) for all options.

### Usage

To publish metrics from your application worker:

```php
<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner;
use Spiral\RoadRunner\Metrics\Collector;
use Spiral\RoadRunner\Metrics\Metrics;

include 'vendor/autoload.php';

$factory = new Psr17Factory();
$worker = new RoadRunner\Http\PSR7Worker(RoadRunner\Worker::create(), $factory, $factory, $factory);

# Create metrics client
$metrics = new Metrics(
    RPC::create(RoadRunner\Environment::fromGlobals()->getRPCAddress()),
);

# Declare counter
$metrics->declare(
    'http_requests',
    Collector::counter()
        ->withHelp('Collected HTTP requests.')
        ->withLabels('status', 'method'),
);

while ($request = $worker->waitRequest()) {
    try {
        $response = new Response();
        $response->getBody()->write('hello world');

        # Publish metrics for each request with labels (status, method)
        $metrics->add('http_requests', 1, [
            (string) $response->getStatusCode(),
            $request->getMethod(),
        ]);

        $worker->respond($response);
    } catch (\Throwable $e) {
        $worker->getWorker()->error((string) $e);

        $metrics->add('http_requests', 1, ['503', $request->getMethod()]);
    }
}
```

Collectors are created with `Collector::counter()`, `Collector::gauge()`, `Collector::histogram(...$buckets)` and
`Collector::summary()`; values are published with `add()`, `sub()`, `set()` and `observe()`.

## Retries and error suppression

`MetricsFactory` wraps the client according to `MetricsOptions`: it retries failed calls (3 attempts by default),
can suppress and log exceptions, and can skip waiting for RPC responses when given an `AsyncRPCInterface`:

```php
use Spiral\RoadRunner\Metrics\MetricsFactory;
use Spiral\RoadRunner\Metrics\MetricsOptions;

$metrics = MetricsFactory::createMetrics(
    $rpc,
    new MetricsOptions(
        retryAttempts: 3,
        retrySleepMicroseconds: 50,
        suppressExceptions: true,
        ignoreResponsesWherePossible: true,
    ),
    $logger, // any PSR-3 logger
);
```

<a href="https://spiral.dev/">
<img src="https://user-images.githubusercontent.com/773481/220979012-e67b74b5-3db1-41b7-bdb0-8a042587dedc.jpg" alt="try Spiral Framework" />
</a>
