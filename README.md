# Tactician Metrics

![Continuous Integration](https://github.com/wyrihaximus/php-metrics-tactician/workflows/Continuous%20Integration/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/wyrihaximus/metrics-tactician/v/stable.png)](https://packagist.org/packages/wyrihaximus/metrics-tactician)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/metrics-tactician/downloads.png)](https://packagist.org/packages/wyrihaximus/metrics-tactician/stats)
[![Code Coverage](https://coveralls.io/repos/github/WyriHaximus/php-metrics-tactician/badge.svg?branchmaster)](https://coveralls.io/github/WyriHaximus/php-metrics-tactician?branch=master)
[![Type Coverage](https://shepherd.dev/github/WyriHaximus/php-metrics-tactician/coverage.svg)](https://shepherd.dev/github/WyriHaximus/php-metrics-tactician)
[![License](https://poser.pugx.org/wyrihaximus/metrics-tactician/license.png)](https://packagist.org/packages/wyrihaximus/metrics-tactician)

# Installation

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/metrics-tactician
```

# Usage

[`wyrihaximus/metrics`](https://github.com/WyriHaximus/php-metrics) middleware for [`Tactician`](https://tactician.thephpleague.com/).

```php
<?php

declare(strict_types=1);

use WyriHaximus\Metrics\Factory;
use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Tactician\CollectorMiddleware;
use WyriHaximus\Metrics\Tactician\Metrics;

$registry  = Factory::create();
$metrics   = Metrics::create($registry, new Label('bus', 'name of your command bus'));
$collector = new CollectorMiddleware($metrics); // Toss this in the collection of middleware you pass to Tactician
```

# License

The MIT License (MIT)

Copyright (c) 2020 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
