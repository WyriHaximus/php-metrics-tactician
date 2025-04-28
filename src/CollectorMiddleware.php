<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics\Tactician;

use League\Tactician\Middleware;
use Throwable;
use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Registry;

use function get_class;
use function hrtime;

final class CollectorMiddleware implements Middleware
{
    /** @var array<Label> */
    private array $defaultLabels;

    private Registry\Gauges $inflight;
    private Registry\Counters $commands;
    private Registry\Summaries $executionTime;

    public function __construct(Metrics $metrics)
    {
        $this->defaultLabels = $metrics->defaultLabels();
        $this->inflight      = $metrics->inflight();
        $this->commands      = $metrics->commands();
        $this->executionTime = $metrics->executionTime();
    }

    // phpcs:disable

    /**
     * @phpstan-ignore shipmonk.missingNativeReturnTypehint
     */
    public function execute($command, callable $next)
    {
        $class = get_class($command);
        $gauge = $this->inflight->gauge(
            new Label('command', $class),
            ...$this->defaultLabels
        );
        $gauge->incr();

        $time = hrtime(true);

        try {
            $labels = array_merge([
                new Label('command', $class),
                new Label('result', 'success'),
            ], $this->defaultLabels);
            $result = $next($command);
            $this->executionTime->summary(...$labels)->observe((hrtime(true) - $time) / 1e+9);
            $this->commands->counter(...$labels)->incr();

            return $result;
        } catch (Throwable $throwable) {
            $labels = array_merge([
                new Label('command', $class),
                new Label('result', 'error'),
            ], $this->defaultLabels);
            $this->executionTime->summary(...$labels)->observe((hrtime(true) - $time) / 1e+9);
            $this->commands->counter(...$labels)->incr();

            throw $throwable;
        } finally {
            $gauge->dcr();
        }
    }
}
