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
        $loggerInstrumentation = new LoggerInstrumentation($mockLogger);

        $this->assertInstanceOf(LoggerInstrumentation::class, $loggerInstrumentation);
    }

    public function testLoggerPropertyReturnsCorrectLogger(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $loggerInstrumentation = new LoggerInstrumentation($mockLogger);

        $result = $loggerInstrumentation->logger;

        $this->assertSame($mockLogger, $result);
    }

    public function testLoggerPropertyReturnsSameInstanceOnMultipleCalls(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $loggerInstrumentation = new LoggerInstrumentation($mockLogger);

        $firstCall = $loggerInstrumentation->logger;
        $secondCall = $loggerInstrumentation->logger;

        $this->assertSame($firstCall, $secondCall);
        $this->assertSame($mockLogger, $firstCall);
        $this->assertSame($mockLogger, $secondCall);
    }

    public function testImplementsInstrumentationInterface(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $loggerInstrumentation = new LoggerInstrumentation($mockLogger);

        $this->assertInstanceOf(
            \App\Shared\Infrastructure\Instrumentation\Instrumentation::class,
            $loggerInstrumentation
        );
    }

    public function testLoggerPropertyHasPropertyHook(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $loggerInstrumentation = new LoggerInstrumentation($mockLogger);

        // Test that the logger property is accessible and has property hook functionality
        $reflectionClass = new \ReflectionClass($loggerInstrumentation);
        $reflectionProperty = $reflectionClass->getProperty('logger');

        $this->assertTrue($reflectionProperty->isPublic());

        // Test direct property access works (using property hook)
        $this->assertSame($mockLogger, $loggerInstrumentation->logger);

        // Test that the backing property is readonly
        $backingProperty = $reflectionClass->getProperty('loggerInstance');
        $this->assertTrue($backingProperty->isReadOnly());
    }
}
