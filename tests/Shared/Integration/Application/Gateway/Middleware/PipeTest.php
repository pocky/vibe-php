<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Middleware\Pipe;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class PipeTest extends TestCase
{
    public function testConstructorWithEmptyMiddlewares(): void
    {
        $pipe = new Pipe([]);
        $this->assertInstanceOf(Pipe::class, $pipe);
    }

    public function testConstructorWithMiddlewares(): void
    {
        $middleware = fn (GatewayRequest $gatewayRequest, callable $next) => $next($gatewayRequest);
        $pipe = new Pipe([$middleware]);

        $this->assertInstanceOf(Pipe::class, $pipe);
    }

    public function testInvokeWithSingleMiddleware(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $middleware = (fn (GatewayRequest $gatewayRequest, callable|null $next = null): \PHPUnit\Framework\MockObject\MockObject => $mockResponse);

        $pipe = new Pipe([$middleware]);
        $gatewayResponse = $pipe($mockRequest);

        $this->assertSame($mockResponse, $gatewayResponse);
    }

    public function testInvokeWithMultipleMiddlewares(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $callOrder = [];

        $middleware1 = function (GatewayRequest $gatewayRequest, callable $next) use (&$callOrder) {
            $callOrder[] = 'first';

            return $next($gatewayRequest);
        };

        $middleware2 = function (GatewayRequest $gatewayRequest, callable $next) use (&$callOrder) {
            $callOrder[] = 'second';

            return $next($gatewayRequest);
        };

        $finalHandler = function (GatewayRequest $gatewayRequest) use (&$callOrder, $mockResponse): \PHPUnit\Framework\MockObject\MockObject {
            $callOrder[] = 'final';

            return $mockResponse;
        };

        $pipe = new Pipe([$middleware1, $middleware2]);
        $gatewayResponse = $pipe($mockRequest, $finalHandler);

        $this->assertSame($mockResponse, $gatewayResponse);
        $this->assertSame(['first', 'second', 'final'], $callOrder);
    }

    public function testInvokeWithCustomNext(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $customNext = (fn (GatewayRequest $gatewayRequest): \PHPUnit\Framework\MockObject\MockObject => $mockResponse);

        $pipe = new Pipe([]);
        $gatewayResponse = $pipe($mockRequest, $customNext);

        $this->assertSame($mockResponse, $gatewayResponse);
    }

    public function testInvokeWithoutNextThrowsException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $pipe = new Pipe([]);

        $this->expectException(InvalidArgumentException::class);
        $pipe($mockRequest);
    }

    public function testMiddlewareExecutionOrder(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $executionOrder = [];

        // Middleware that adds to execution order
        $middleware1 = function (GatewayRequest $gatewayRequest, callable $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware1_before';
            $response = $next($gatewayRequest);
            $executionOrder[] = 'middleware1_after';

            return $response;
        };

        $middleware2 = function (GatewayRequest $gatewayRequest, callable $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware2_before';
            $response = $next($gatewayRequest);
            $executionOrder[] = 'middleware2_after';

            return $response;
        };

        $finalHandler = function (GatewayRequest $gatewayRequest) use (&$executionOrder, $mockResponse): \PHPUnit\Framework\MockObject\MockObject {
            $executionOrder[] = 'handler';

            return $mockResponse;
        };

        $pipe = new Pipe([$middleware1, $middleware2]);
        $pipe($mockRequest, $finalHandler);

        $expectedOrder = [
            'middleware1_before',
            'middleware2_before',
            'handler',
            'middleware2_after',
            'middleware1_after',
        ];

        $this->assertSame($expectedOrder, $executionOrder);
    }
}
