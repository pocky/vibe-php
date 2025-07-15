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
    private const string CALLABLE_MIDDLEWARE_CLASS = CallableMiddleware::class;
    private GatewayInstrumentation $mockInstrumentation;
    private DefaultLogger $logger;

    protected function setUp(): void
    {
        $this->mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $this->logger = new DefaultLogger($this->mockInstrumentation);
    }

    public function testConstructorSetsInstrumentation(): void
    {
        $logger = new DefaultLogger($this->mockInstrumentation);
        $this->assertInstanceOf(DefaultLogger::class, $logger);
    }

    public function testInvokeLogsStartAndSuccess(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = fn (GatewayRequest $request) => $mockResponse;

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

        $result = ($this->logger)($mockRequest, $next);

        $this->assertSame($mockResponse, $result);
    }

    public function testInvokeReturnsResponseFromNext(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = fn (GatewayRequest $request) => $mockResponse;

        $this->mockInstrumentation
            ->method('start');

        $this->mockInstrumentation
            ->method('success');

        $result = ($this->logger)($mockRequest, $next);

        $this->assertSame($mockResponse, $result);
    }

    public function testInvokeCallsInstrumentationInCorrectOrder(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $callOrder = [];

        $next = function (GatewayRequest $request) use ($mockResponse, &$callOrder) {
            $callOrder[] = 'next_called';

            return $mockResponse;
        };

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('start')
            ->with($mockRequest)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'start_called';
            });

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('success')
            ->with($mockResponse)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'success_called';
            });

        ($this->logger)($mockRequest, $next);

        $this->assertSame(['start_called', 'next_called', 'success_called'], $callOrder);
    }

    public function testInvokePassesThroughExceptions(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $expectedException = new \RuntimeException('Test exception');

        $next = function (GatewayRequest $request) use ($expectedException) {
            throw $expectedException;
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

        ($this->logger)($mockRequest, $next);
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

        $next = fn (GatewayRequest $request) => $mockResponse;

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('start')
            ->with($this->callback(fn (GatewayRequest $request) => $request->data() === [
                'key' => 'value',
            ]));

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('success')
            ->with($this->callback(fn (GatewayResponse $response) => $response->data() === [
                'result' => 'success',
            ]));

        $result = ($this->logger)($mockRequest, $next);

        $this->assertSame($mockResponse, $result);
    }
}
