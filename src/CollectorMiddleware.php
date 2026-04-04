<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics\Tactician;

use League\Tactician\Middleware;
use Throwable;
use WyriHaximus\Metrics\Label;

use function hrtime;

/** @api */
final readonly class CollectorMiddleware implements Middleware
{
    public function __construct(private Metrics $metrics)
    {
    }

    // phpcs:disable

    /**
     * @phpstan-ignore shipmonk.missingNativeReturnTypehint,typeCoverage.paramTypeCoverage,typeCoverage.returnTypeCoverage
     */
    public function execute($command, callable $next)
    {
        $class = $command::class;
        $gauge = $this->metrics->inflight->gauge(
            new Label('command', $class),
            ...$this->metrics->defaultLabels
        );
        $gauge->incr();

        $time = hrtime(true);

        try {
            $labels = array_merge([
                new Label('command', $class),
                new Label('result', 'success'),
            ], $this->metrics->defaultLabels);
            $result = $next($command);
            $this->metrics->executionTime->summary(...$labels)->observe((hrtime(true) - $time) / 1e+9);
            $this->metrics->commands->counter(...$labels)->incr();

            return $result;
        } catch (Throwable $throwable) {
            $labels = array_merge([
                new Label('command', $class),
                new Label('result', 'error'),
            ], $this->metrics->defaultLabels);
            $this->metrics->executionTime->summary(...$labels)->observe((hrtime(true) - $time) / 1e+9);
            $this->metrics->commands->counter(...$labels)->incr();

            throw $throwable;
        } finally {
            $gauge->dcr();
        }
    }
}
