<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use PHPUnit\Framework\TestCase;

final class DefaultLoggerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $mockInstrumentation;

    private DefaultLogger $defaultLogger;

    protected function setUp(): void
    {
        $this->mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $this->defaultLogger = new DefaultLogger($this->mockInstrumentation);
    }

    public function testConstructorSetsInstrumentation(): void
    {
        $defaultLogger = new DefaultLogger($this->mockInstrumentation);
        $this->assertInstanceOf(DefaultLogger::class, $defaultLogger);
    }

    public function testInvokeLogsStartAndSuccess(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = fn (GatewayRequest $gatewayRequest): \PHPUnit\Framework\MockObject\MockObject => $mockResponse;

        // Expect start to be called first
        $this->mockInstrumentation
            ->expects($this->once())
            ->method('start')
            ->with($this->identicalTo($mockRequest));

        // Expect success to be called after
        $this->mockInstrumentation
            ->expects($this->once())
            ->method('success')
            ->with($this->identicalTo($mockResponse));

        $gatewayResponse = ($this->defaultLogger)($mockRequest, $next);

        $this->assertSame($mockResponse, $gatewayResponse);
    }

    public function testInvokeReturnsResponseFromNext(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = fn (GatewayRequest $gatewayRequest): \PHPUnit\Framework\MockObject\MockObject => $mockResponse;

        $this->mockInstrumentation
            ->method('start');

        $this->mockInstrumentation
            ->method('success');

        $gatewayResponse = ($this->defaultLogger)($mockRequest, $next);

        $this->assertSame($mockResponse, $gatewayResponse);
    }

    public function testInvokeCallsInstrumentationInCorrectOrder(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $callOrder = [];

        $next = function (GatewayRequest $gatewayRequest) use ($mockResponse, &$callOrder): \PHPUnit\Framework\MockObject\MockObject {
            $callOrder[] = 'next_called';

            return $mockResponse;
        };

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('start')
            ->with($mockRequest)
            ->willReturnCallback(function () use (&$callOrder): void {
                $callOrder[] = 'start_called';
            });

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('success')
            ->with($mockResponse)
            ->willReturnCallback(function () use (&$callOrder): void {
                $callOrder[] = 'success_called';
            });

        ($this->defaultLogger)($mockRequest, $next);

        $this->assertSame(['start_called', 'next_called', 'success_called'], $callOrder);
    }

    public function testInvokePassesThroughExceptions(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $runtimeException = new \RuntimeException('Test exception');

        $next = function (GatewayRequest $gatewayRequest) use ($runtimeException): never {
            throw $runtimeException;
        };

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('start')
            ->with($mockRequest);

        // Success should not be called when exception is thrown
        $this->mockInstrumentation
            ->expects($this->never())
            ->method('success');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        ($this->defaultLogger)($mockRequest, $next);
    }

    public function testInvokeWithDifferentRequestAndResponseTypes(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockRequest->method('data')->willReturn([
            'key' => 'value',
        ]);

        $mockResponse = $this->createMock(GatewayResponse::class);
        $mockResponse->method('data')->willReturn([
            'result' => 'success',
        ]);

        $next = fn (GatewayRequest $gatewayRequest): \PHPUnit\Framework\MockObject\MockObject => $mockResponse;

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('start')
            ->with($this->callback(fn (GatewayRequest $gatewayRequest): bool => $gatewayRequest->data() === [
                'key' => 'value',
            ]));

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('success')
            ->with($this->callback(fn (GatewayResponse $gatewayResponse): bool => $gatewayResponse->data() === [
                'result' => 'success',
            ]));

        $result = ($this->defaultLogger)($mockRequest, $next);

        $this->assertSame($mockResponse, $result);
    }
}
