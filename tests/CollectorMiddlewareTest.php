<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\Metrics\Tactician;

use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;
use WyriHaximus\Metrics\InMemory\Registry;
use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Printer\Prometheus;
use WyriHaximus\Metrics\Tactician\CollectorMiddleware;

use function Safe\sleep;

final class CollectorMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function success(): void
    {
        $registry  = new Registry();
        $collector = new CollectorMiddleware($registry, new Label('name', 'test'));

        $metrics = $registry->print(new Prometheus());
        self::assertSame("\n\n\n", $metrics);

        $collector->execute(new stdClass(), static function (): stdClass {
            sleep(1);

            return new stdClass();
        });

        $metrics = $registry->print(new Prometheus());
        self::assertStringContainsString('tactician_commands_total{command="stdClass",name="test",result="success"} 1', $metrics);
        self::assertStringContainsString('tactician_commands_inflight{command="stdClass",name="test"} 0', $metrics);
        self::assertStringContainsString('tactician_command_execution_times_bucket{le="1",command="stdClass",name="test",result="success"} 0', $metrics);
        self::assertStringContainsString('tactician_command_execution_times_bucket{le="2.5",command="stdClass",name="test",result="success"} 1', $metrics);
    }

    /**
     * @test
     */
    public function error(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('When in doubt, C4');

        $registry  = new Registry();
        $collector = new CollectorMiddleware($registry, new Label('name', 'test'));

        $metrics = $registry->print(new Prometheus());
        self::assertSame("\n\n\n", $metrics);

        try {
            $collector->execute(new stdClass(), static function (): void {
                sleep(1);

                /** @phpstan-ignore-next-line */
                throw new Exception('When in doubt, C4');
            });
        } catch (Throwable $throwable) {
            throw $throwable;
        } finally {
            $metrics = $registry->print(new Prometheus());
            self::assertStringContainsString('tactician_commands_total{command="stdClass",name="test",result="error"} 1', $metrics);
            self::assertStringContainsString('tactician_commands_inflight{command="stdClass",name="test"} 0', $metrics);
            self::assertStringContainsString('tactician_command_execution_times_bucket{le="0.001",command="stdClass",name="test",result="error"} 0', $metrics);
            self::assertStringContainsString('tactician_command_execution_times_bucket{le="2.5",command="stdClass",name="test",result="error"} 1', $metrics);
        }
    }
}
