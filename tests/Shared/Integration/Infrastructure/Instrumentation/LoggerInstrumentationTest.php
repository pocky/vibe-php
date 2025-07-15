<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\Instrumentation;

use App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoggerInstrumentationTest extends TestCase
{
    public function testConstructorSetsLogger(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $instrumentation = new LoggerInstrumentation($mockLogger);

        $this->assertInstanceOf(LoggerInstrumentation::class, $instrumentation);
    }

    public function testLoggerPropertyReturnsCorrectLogger(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $instrumentation = new LoggerInstrumentation($mockLogger);

        $result = $instrumentation->logger;

        $this->assertSame($mockLogger, $result);
    }

    public function testLoggerPropertyReturnsSameInstanceOnMultipleCalls(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $instrumentation = new LoggerInstrumentation($mockLogger);

        $firstCall = $instrumentation->logger;
        $secondCall = $instrumentation->logger;

        $this->assertSame($firstCall, $secondCall);
        $this->assertSame($mockLogger, $firstCall);
        $this->assertSame($mockLogger, $secondCall);
    }

    public function testImplementsInstrumentationInterface(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $instrumentation = new LoggerInstrumentation($mockLogger);

        $this->assertInstanceOf(
            \App\Shared\Infrastructure\Instrumentation\Instrumentation::class,
            $instrumentation
        );
    }

    public function testLoggerPropertyHasPropertyHook(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $instrumentation = new LoggerInstrumentation($mockLogger);

        // Test that the logger property is accessible and has property hook functionality
        $reflection = new \ReflectionClass($instrumentation);
        $property = $reflection->getProperty('logger');

        $this->assertTrue($property->isPublic());

        // Test direct property access works (using property hook)
        $this->assertSame($mockLogger, $instrumentation->logger);

        // Test that the backing property is readonly
        $backingProperty = $reflection->getProperty('loggerInstance');
        $this->assertTrue($backingProperty->isReadOnly());
    }
}
