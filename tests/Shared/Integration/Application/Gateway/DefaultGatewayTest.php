<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Application\Gateway;

use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use PHPUnit\Framework\TestCase;

final class DefaultGatewayTest extends TestCase
{
    public function testConstructorSetsMiddlewares(): void
    {
        $middlewares = [
            fn (GatewayRequest $gatewayRequest, callable $next) => $next($gatewayRequest),
        ];

        $gateway = new class($middlewares) extends DefaultGateway {};

        $this->assertInstanceOf(DefaultGateway::class, $gateway);
    }

    public function testInvokeProcessesRequestThroughPipe(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockRequest->method('data')->willReturn([
            'test' => 'data',
        ]);

        $mockResponse = $this->createMock(GatewayResponse::class);
        $mockResponse->method('data')->willReturn([
            'result' => 'success',
        ]);

        $middleware = (fn (GatewayRequest $gatewayRequest, callable|null $next = null): \PHPUnit\Framework\MockObject\MockObject =>
            // Simulate middleware that returns a response
            $mockResponse);

        $gateway = new class([$middleware]) extends DefaultGateway {};
        $gatewayResponse = $gateway($mockRequest);

        $this->assertInstanceOf(GatewayResponse::class, $gatewayResponse);
        $this->assertSame($mockResponse, $gatewayResponse);
    }

    public function testInvokeWithMultipleMiddlewares(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $callOrder = [];

        $middleware1 = function (GatewayRequest $gatewayRequest, callable $next) use (&$callOrder) {
            $callOrder[] = 'middleware1';

            return $next($gatewayRequest);
        };

        $middleware2 = function (GatewayRequest $gatewayRequest, callable|null $next = null) use (&$callOrder, $mockResponse): \PHPUnit\Framework\MockObject\MockObject {
            $callOrder[] = 'middleware2';

            return $mockResponse;
        };

        $gateway = new class([$middleware1, $middleware2]) extends DefaultGateway {};
        $gatewayResponse = $gateway($mockRequest);

        $this->assertSame($mockResponse, $gatewayResponse);
        $this->assertSame(['middleware1', 'middleware2'], $callOrder);
    }

    public function testInvokeWithEmptyMiddlewares(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $gateway = new class([]) extends DefaultGateway {};

        $this->expectException(\InvalidArgumentException::class);
        $gateway($mockRequest);
    }
}
