<a href="https://roadrunner.dev" target="_blank">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
    <img align="center" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8">
  </picture>
</a>

# RoadRunner Metrics Plugin

[![PHP Version Require](https://poser.pugx.org/spiral/roadrunner-metrics/require/php)](https://packagist.org/packages/spiral/roadrunner-metrics)
[![Latest Stable Version](https://poser.pugx.org/spiral/roadrunner-metrics/version)](https://packagist.org/packages/spiral/roadrunner-metrics)
[![phpunit](https://github.com/spiral/roadrunner-metrics/actions/workflows/phpunit.yml/badge.svg)](https://github.com/spiral/roadrunner-metrics/actions)
[![psalm](https://github.com/spiral/roadrunner-metrics/actions/workflows/psalm.yml/badge.svg)](https://github.com/spiral/roadrunner-metrics/actions)
[![Total Downloads](https://poser.pugx.org/spiral/roadrunner-metrics/downloads)](https://packagist.org/packages/spiral/roadrunner-metrics)

This repository contains the codebase PHP bridge using RoadRunner Metrics plugin.

## Installation:

To install RoadRunner extension:

```bash
composer require spiral/roadrunner-metrics
```

You can use the convenient installer to download the latest available compatible version of RoadRunner assembly:

```bash
composer require spiral/roadrunner-cli --dev
vendor/bin/rr get
```

## Configuration

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

## Usage

To publish metrics from your application worker:

```php
<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory;use Spiral\Goridge;use Spiral\RoadRunner;

include "vendor/autoload.php";

$worker = new RoadRunner\Http\PSR7Worker(
    RoadRunner\Worker::create(),
    new Factory\Psr17Factory(),
    new Factory\Psr17Factory(),
    new Factory\Psr17Factory()
);

# Create metrics client
$metrics = new RoadRunner\Metrics\Metrics(
    Goridge\RPC\RPC::create(RoadRunner\Environment::fromGlobals()->getRPCAddress())
);

# Declare counter
$metrics->declare(
    'http_requests',
    RoadRunner\Metrics\Collector::counter()
        ->withHelp('Collected HTTP requests.')
        ->withLabels('status', 'method'),
);

while ($req = $worker->waitRequest()) {
    try {
        $response = new \Nyholm\Psr7\Response();
        $response->getBody()->write("hello world");

        # Publish metrics for each request with labels (status, method)
        $metrics->add('http_requests', 1, [
            $response->getStatusCode(),
            $req->getMethod(),
        ]);

        $worker->respond($rsp);
    } catch (\Throwable $e) {
        $worker->getWorker()->error((string)$e);

        $metrics->add('http_requests', 1, [503,$req->getMethod(),]);
    }
}
```

<a href="https://spiral.dev/">
<img src="https://user-images.githubusercontent.com/773481/220979012-e67b74b5-3db1-41b7-bdb0-8a042587dedc.jpg" alt="try Spiral Framework" />
</a>

## License:

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained
by [Spiral Scout](https://spiralscout.com).
