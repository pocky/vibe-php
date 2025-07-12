<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application\Gateway;

use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use PHPUnit\Framework\TestCase;

final class DefaultGatewayTest extends TestCase
{
    public function testConstructorSetsMiddlewares(): void
    {
        $middlewares = [
            fn (GatewayRequest $request, callable $next) => $next($request),
        ];

        $gateway = new DefaultGateway($middlewares);

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

        $middleware = (fn (GatewayRequest $request, callable|null $next = null) =>
            // Simulate middleware that returns a response
            $mockResponse);

        $gateway = new DefaultGateway([$middleware]);
        $result = $gateway($mockRequest);

        $this->assertInstanceOf(GatewayResponse::class, $result);
        $this->assertSame($mockResponse, $result);
    }

    public function testInvokeWithMultipleMiddlewares(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $callOrder = [];

        $middleware1 = function (GatewayRequest $request, callable $next) use (&$callOrder) {
            $callOrder[] = 'middleware1';

            return $next($request);
        };

        $middleware2 = function (GatewayRequest $request, callable|null $next = null) use (&$callOrder, $mockResponse) {
            $callOrder[] = 'middleware2';

            return $mockResponse;
        };

        $gateway = new DefaultGateway([$middleware1, $middleware2]);
        $result = $gateway($mockRequest);

        $this->assertSame($mockResponse, $result);
        $this->assertSame(['middleware1', 'middleware2'], $callOrder);
    }

    public function testInvokeWithEmptyMiddlewares(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $gateway = new DefaultGateway([]);

        $this->expectException(\InvalidArgumentException::class);
        $gateway($mockRequest);
    }
}
