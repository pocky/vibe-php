<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayException;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use PHPUnit\Framework\TestCase;

final class DefaultErrorHandlerTest extends TestCase
{
    private GatewayInstrumentation $mockInstrumentation;
    private DefaultErrorHandler $errorHandler;

    protected function setUp(): void
    {
        $this->mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $this->errorHandler = new DefaultErrorHandler(
            $this->mockInstrumentation,
            'TestContext',
            'TestEntity',
            'create'
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $errorHandler = new DefaultErrorHandler(
            $this->mockInstrumentation,
            'UserContext',
            'User',
            'update'
        );

        $this->assertInstanceOf(DefaultErrorHandler::class, $errorHandler);
    }

    public function testInvokeReturnsResponseOnSuccess(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockResponse = $this->createMock(GatewayResponse::class);

        $next = (fn (GatewayRequest $request) => $mockResponse);

        $result = ($this->errorHandler)($mockRequest, $next);

        $this->assertSame($mockResponse, $result);
    }

    public function testInvokeCatchesExceptionAndThrowsGatewayException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \RuntimeException('Database error');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('error')
            ->with($mockRequest, 'Database error');

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Error during create process for TestContext TestEntity');

        ($this->errorHandler)($mockRequest, $next);
    }

    public function testInvokeRecordsErrorInInstrumentation(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \InvalidArgumentException('Validation failed');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        $this->mockInstrumentation
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->identicalTo($mockRequest),
                $this->equalTo('Validation failed')
            );

        try {
            ($this->errorHandler)($mockRequest, $next);
        } catch (GatewayException) {
            // Expected exception
        }
    }

    public function testGatewayExceptionContainsPreviousException(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \Exception('Original error');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        $this->mockInstrumentation
            ->method('error');

        try {
            ($this->errorHandler)($mockRequest, $next);
            $this->fail('Expected GatewayException to be thrown');
        } catch (GatewayException $gatewayException) {
            $this->assertSame($originalException, $gatewayException->getPrevious());
        }
    }

    public function testErrorMessageFormatting(): void
    {
        $errorHandler = new DefaultErrorHandler(
            $this->mockInstrumentation,
            'UserContext',
            'User',
            'delete'
        );

        $mockRequest = $this->createMock(GatewayRequest::class);
        $originalException = new \Exception('Not found');

        $next = function (GatewayRequest $request) use ($originalException) {
            throw $originalException;
        };

        $this->mockInstrumentation
            ->method('error');

        try {
            $errorHandler($mockRequest, $next);
            $this->fail('Expected GatewayException to be thrown');
        } catch (GatewayException $gatewayException) {
            $this->assertStringContainsString('Error during delete process for UserContext User', $gatewayException->getMessage());
        }
    }
}
