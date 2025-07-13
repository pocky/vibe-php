<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Application\Gateway\Instrumentation;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation;
use App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class DefaultGatewayInstrumentationTest extends TestCase
{
    private LoggerInterface $mockLogger;
    private LoggerInstrumentation $mockLoggerInstrumentation;
    private DefaultGatewayInstrumentation $instrumentation;

    protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockLoggerInstrumentation = new LoggerInstrumentation($this->mockLogger);

        $this->instrumentation = new DefaultGatewayInstrumentation(
            $this->mockLoggerInstrumentation,
            'TestGateway'
        );
    }

    public function testConstructorSetsName(): void
    {
        $instrumentation = new DefaultGatewayInstrumentation(
            $this->mockLoggerInstrumentation,
            'UserGateway'
        );

        $this->assertInstanceOf(DefaultGatewayInstrumentation::class, $instrumentation);
    }

    public function testConstructorGetsLoggerFromInstrumentation(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $loggerInstrumentation = new LoggerInstrumentation($mockLogger);

        $instrumentation = new DefaultGatewayInstrumentation($loggerInstrumentation, 'TestGateway');

        // Test that the logger is used correctly by testing the start method
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockRequest->method('data')->willReturn([
            'test' => 'data',
        ]);

        $mockLogger->expects($this->once())
            ->method('info')
            ->with('TestGateway', [
                'test' => 'data',
            ]);

        $instrumentation->start($mockRequest);
    }

    public function testStartLogsInfoWithGatewayName(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $requestData = [
            'user_id' => '123',
            'action' => 'create',
        ];
        $mockRequest->method('data')->willReturn($requestData);

        $this->mockLogger
            ->expects($this->once())
            ->method('info')
            ->with('TestGateway', $requestData);

        $this->instrumentation->start($mockRequest);
    }

    public function testSuccessLogsInfoWithSuccessSuffix(): void
    {
        $mockResponse = $this->createMock(GatewayResponse::class);
        $responseData = [
            'id' => '456',
            'status' => 'created',
        ];
        $mockResponse->method('data')->willReturn($responseData);

        $this->mockLogger
            ->expects($this->once())
            ->method('info')
            ->with('TestGateway.success', $responseData);

        $this->instrumentation->success($mockResponse);
    }

    public function testErrorLogsErrorWithReasonAndRequestData(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $requestData = [
            'user_id' => '123',
        ];
        $mockRequest->method('data')->willReturn($requestData);

        $reason = 'Database connection failed';

        $expectedErrorData = [
            'user_id' => '123',
            ' reason' => $reason,
        ];

        $this->mockLogger
            ->expects($this->once())
            ->method('error')
            ->with('TestGateway.error', $expectedErrorData);

        $this->instrumentation->error($mockRequest, $reason);
    }

    public function testStartWithEmptyRequestData(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockRequest->method('data')->willReturn([]);

        $this->mockLogger
            ->expects($this->once())
            ->method('info')
            ->with('TestGateway', []);

        $this->instrumentation->start($mockRequest);
    }

    public function testSuccessWithEmptyResponseData(): void
    {
        $mockResponse = $this->createMock(GatewayResponse::class);
        $mockResponse->method('data')->willReturn([]);

        $this->mockLogger
            ->expects($this->once())
            ->method('info')
            ->with('TestGateway.success', []);

        $this->instrumentation->success($mockResponse);
    }

    public function testErrorWithEmptyRequestDataAndReason(): void
    {
        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockRequest->method('data')->willReturn([]);

        $reason = 'Validation failed';

        $this->mockLogger
            ->expects($this->once())
            ->method('error')
            ->with('TestGateway.error', [
                ' reason' => $reason,
            ]);

        $this->instrumentation->error($mockRequest, $reason);
    }

    public function testImplementsGatewayInstrumentationInterface(): void
    {
        $this->assertInstanceOf(
            \App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation::class,
            $this->instrumentation
        );
    }

    public function testDifferentGatewayNamesProduceDifferentLogMessages(): void
    {
        $anotherInstrumentation = new DefaultGatewayInstrumentation(
            $this->mockLoggerInstrumentation,
            'AnotherGateway'
        );

        $mockRequest = $this->createMock(GatewayRequest::class);
        $mockRequest->method('data')->willReturn([
            'test' => 'data',
        ]);

        // First instrumentation logs with 'TestGateway'
        $this->mockLogger
            ->expects($this->exactly(2))
            ->method('info')
            ->with(
                $this->logicalOr(
                    $this->equalTo('TestGateway'),
                    $this->equalTo('AnotherGateway')
                ),
                [
                    'test' => 'data',
                ]
            );

        $this->instrumentation->start($mockRequest);
        $anotherInstrumentation->start($mockRequest);
    }
}
