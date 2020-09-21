<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics\Tactician;

use League\Tactician\Middleware;
use Throwable;
use WyriHaximus\Metrics\Histogram\Buckets;
use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Registry;

use function get_class;
use function hrtime;

final class CollectorMiddleware implements Middleware
{
    private const EXECUTION_TIME_BUCKETS = [
        0.001,
        0.0025,
        0.005,
        0.01,
        0.025,
        0.05,
        0.1,
        0.25,
        0.5,
        1,
        2.5,
        5,
        10,
    ];

    private string $name;
    private Registry\Gauges $inflight;
    private Registry\Counters $commands;
    private Registry\Histograms $executionTime;

    public function __construct(string $name, Registry $registry)
    {
        $this->name          = $name;
        $this->inflight      = $registry->gauge(
            'tactician_commands_inflight',
            'The number of HTTP requests that are currently inflight within the application',
            new Label\Name('name'),
            new Label\Name('command'),
        );
        $this->commands      = $registry->counter(
            'tactician_commands',
            'The number of HTTP requests handled by HTTP request method and response status code',
            new Label\Name('name'),
            new Label\Name('command'),
            new Label\Name('result'),
        );
        $this->executionTime = $registry->histogram(
            'tactician_command_execution_times',
            'The time it took to come to a response by HTTP request method and response status code',
            new Buckets(...self::EXECUTION_TIME_BUCKETS),
            new Label\Name('name'),
            new Label\Name('command'),
            new Label\Name('result'),
        );
    }

    // phpcs:disable
    public function execute($command, callable $next)
    {
        $gauge = $this->inflight->gauge(
            new Label('name', $this->name),
            new Label('command', get_class($command)),
        );
        $gauge->incr();

        $time = hrtime(true);

        try {
            $labels = [
                new Label('name', $this->name),
                new Label('command', get_class($command)),
                new Label('result', 'success'),
            ];
            $result = $next($command);
            $this->executionTime->histogram(...$labels)->observe((hrtime(true) - $time) / 1e+9);
            $this->commands->counter(...$labels)->incr();

            return $result;
        } catch (Throwable $throwable) {
            $labels = [
                new Label('name', $this->name),
                new Label('command', get_class($command)),
                new Label('result', 'error'),
            ];
            $this->executionTime->histogram(...$labels)->observe((hrtime(true) - $time) / 1e+9);
            $this->commands->counter(...$labels)->incr();

            throw $throwable;
        } finally {
            $gauge->dcr();
        }
    }
}
