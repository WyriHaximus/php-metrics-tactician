<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics\Tactician;

use WyriHaximus\Metrics\Factory;
use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Registry;

use function array_map;

final readonly class Metrics
{
    /** @var array<Label> */
    private array $defaultLabels;

    public function __construct(private Registry\Gauges $inflight, private Registry\Counters $commands, private Registry\Summaries $executionTime, Label ...$defaultLabels)
    {
        $this->defaultLabels = $defaultLabels;
    }

    public static function create(Registry $registry, Label ...$defaultLabels): self
    {
        $defaultLabelNames = array_map(static fn (Label $label): Label\Name => new Label\Name($label->name()), $defaultLabels);

        return new self(
            $registry->gauge(
                'tactician_commands_inflight',
                'The number of HTTP requests that are currently inflight within the application',
                new Label\Name('command'),
                ...$defaultLabelNames,
            ),
            $registry->counter(
                'tactician_commands',
                'The number of HTTP requests handled by HTTP request method and response status code',
                new Label\Name('command'),
                new Label\Name('result'),
                ...$defaultLabelNames,
            ),
            $registry->summary(
                'tactician_command_execution_times',
                'The time it took to come to a response by HTTP request method and response status code',
                Factory::defaultQuantiles(),
                new Label\Name('command'),
                new Label\Name('result'),
                ...$defaultLabelNames,
            ),
            ...$defaultLabels,
        );
    }

    /** @return array<Label> */
    public function defaultLabels(): array
    {
        return $this->defaultLabels;
    }

    public function inflight(): Registry\Gauges
    {
        return $this->inflight;
    }

    public function commands(): Registry\Counters
    {
        return $this->commands;
    }

    public function executionTime(): Registry\Summaries
    {
        return $this->executionTime;
    }
}
