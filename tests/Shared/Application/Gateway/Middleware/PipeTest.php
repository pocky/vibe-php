<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application\Gateway\Middleware;

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
        $middleware = fn (GatewayRequest $request, callable $next) => $next($request);
        $pipe = new Pipe([$middleware]);

        $this->assertInstanceOf(Pipe::class, $pipe);
    }

    public function testInvokeWithSingleMiddleware(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $middleware = (fn (GatewayRequest $request, callable|null $next = null) => $mockResponse);

        $pipe = new Pipe([$middleware]);
        $result = $pipe($mockRequest);

        $this->assertSame($mockResponse, $result);
    }

    public function testInvokeWithMultipleMiddlewares(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $callOrder = [];

        $middleware1 = function (GatewayRequest $request, callable $next) use (&$callOrder) {
            $callOrder[] = 'first';

            return $next($request);
        };

        $middleware2 = function (GatewayRequest $request, callable $next) use (&$callOrder) {
            $callOrder[] = 'second';

            return $next($request);
        };

        $finalHandler = function (GatewayRequest $request) use (&$callOrder, $mockResponse) {
            $callOrder[] = 'final';

            return $mockResponse;
        };

        $pipe = new Pipe([$middleware1, $middleware2]);
        $result = $pipe($mockRequest, $finalHandler);

        $this->assertSame($mockResponse, $result);
        $this->assertSame(['first', 'second', 'final'], $callOrder);
    }

    public function testInvokeWithCustomNext(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $customNext = (fn (GatewayRequest $request) => $mockResponse);

        $pipe = new Pipe([]);
        $result = $pipe($mockRequest, $customNext);

        $this->assertSame($mockResponse, $result);
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
        $middleware1 = function (GatewayRequest $request, callable $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware1_before';
            $response = $next($request);
            $executionOrder[] = 'middleware1_after';

            return $response;
        };

        $middleware2 = function (GatewayRequest $request, callable $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware2_before';
            $response = $next($request);
            $executionOrder[] = 'middleware2_after';

            return $response;
        };

        $finalHandler = function (GatewayRequest $request) use (&$executionOrder, $mockResponse) {
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
