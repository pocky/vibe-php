<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayException;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use PHPUnit\Framework\TestCase;

final class DefaultErrorHandlerTest extends TestCase
{
    private const string CALLABLE_MIDDLEWARE_CLASS = CallableMiddleware::class;
    private DefaultErrorHandler $errorHandler;

    protected function setUp(): void
    {
        $instrumentation = $this->createMock(GatewayInstrumentation::class);
        $this->errorHandler = new DefaultErrorHandler($instrumentation, 'test', 'entity', 'operation');
    }

    public function testConstructorSetsProperties(): void
    {
        $instrumentation = $this->createMock(GatewayInstrumentation::class);
        $errorHandler = new DefaultErrorHandler($instrumentation, 'test', 'entity', 'operation');

        $this->assertInstanceOf(DefaultErrorHandler::class, $errorHandler);
    }

    public function testInvokeReturnsResponseOnSuccess(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = fn (GatewayRequest $request) => $mockResponse;

        $result = ($this->errorHandler)($mockRequest, $next);

        $this->assertSame($mockResponse, $result);
    }

    public function testInvokeCatchesRuntimeExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \RuntimeException('Database error');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Error during operation process for test entity');

        ($this->errorHandler)($mockRequest, $next);
    }

    public function testInvokeCatchesInvalidArgumentExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \InvalidArgumentException('Validation failed');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Error during operation process for test entity');

        try {
            ($this->errorHandler)($mockRequest, $next);
        } catch (GatewayException $e) {
            $this->assertSame($originalException, $e->getPrevious());
            throw $e;
        }
    }

    public function testGatewayExceptionContainsPreviousException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \Exception('Original error');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        try {
            ($this->errorHandler)($mockRequest, $next);
            $this->fail('Expected GatewayException to be thrown');
        } catch (GatewayException $gatewayException) {
            $this->assertSame($originalException, $gatewayException->getPrevious());
        }
    }

    public function testCatchesGenericExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \Exception('Not found');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        try {
            ($this->errorHandler)($mockRequest, $next);
            $this->fail('Expected GatewayException to be thrown');
        } catch (GatewayException $gatewayException) {
            $this->assertStringContainsString('Error during operation process for test entity', $gatewayException->getMessage());
            $this->assertSame($originalException, $gatewayException->getPrevious());
        }
    }
}
