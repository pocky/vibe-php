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
    private DefaultErrorHandler $defaultErrorHandler;

    protected function setUp(): void
    {
        $instrumentation = $this->createMock(GatewayInstrumentation::class);
        $this->defaultErrorHandler = new DefaultErrorHandler($instrumentation, 'test', 'entity', 'operation');
    }

    public function testConstructorSetsProperties(): void
    {
        $instrumentation = $this->createMock(GatewayInstrumentation::class);
        $defaultErrorHandler = new DefaultErrorHandler($instrumentation, 'test', 'entity', 'operation');

        $this->assertInstanceOf(DefaultErrorHandler::class, $defaultErrorHandler);
    }

    public function testInvokeReturnsResponseOnSuccess(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = fn (GatewayRequest $gatewayRequest): \PHPUnit\Framework\MockObject\MockObject => $mockResponse;

        $gatewayResponse = ($this->defaultErrorHandler)($mockRequest, $next);

        $this->assertSame($mockResponse, $gatewayResponse);
    }

    public function testInvokeCatchesRuntimeExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $runtimeException = new \RuntimeException('Database error');

        $next = function (GatewayRequest $gatewayRequest) use ($runtimeException): never {
            throw $runtimeException;
        };

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Error during operation process for test entity');

        ($this->defaultErrorHandler)($mockRequest, $next);
    }

    public function testInvokeCatchesInvalidArgumentExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $invalidArgumentException = new \InvalidArgumentException('Validation failed');

        $next = function (GatewayRequest $gatewayRequest) use ($invalidArgumentException): never {
            throw $invalidArgumentException;
        };

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Error during operation process for test entity');

        try {
            ($this->defaultErrorHandler)($mockRequest, $next);
        } catch (GatewayException $gatewayException) {
            $this->assertSame($invalidArgumentException, $gatewayException->getPrevious());
            throw $gatewayException;
        }
    }

    public function testGatewayExceptionContainsPreviousException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \Exception('Original error');

        $next = function (GatewayRequest $gatewayRequest) use ($originalException): never {
            throw $originalException;
        };

        try {
            ($this->defaultErrorHandler)($mockRequest, $next);
            $this->fail('Expected GatewayException to be thrown');
        } catch (GatewayException $gatewayException) {
            $this->assertSame($originalException, $gatewayException->getPrevious());
        }
    }

    public function testCatchesGenericExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \Exception('Not found');

        $next = function (GatewayRequest $gatewayRequest) use ($originalException): never {
            throw $originalException;
        };

        try {
            ($this->defaultErrorHandler)($mockRequest, $next);
            $this->fail('Expected GatewayException to be thrown');
        } catch (GatewayException $gatewayException) {
            $this->assertStringContainsString('Error during operation process for test entity', $gatewayException->getMessage());
            $this->assertSame($originalException, $gatewayException->getPrevious());
        }
    }
}
