<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Application\Gateway\Instrumentation;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Instrumentation\AbstractGatewayInstrumentation;
use App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AbstractGatewayInstrumentationTest extends TestCase
{
    private LoggerInterface $logger;
    private LoggerInstrumentation $loggerInstrumentation;
    private AbstractGatewayInstrumentation $instrumentation;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->loggerInstrumentation = $this->createMock(LoggerInstrumentation::class);
        $this->loggerInstrumentation
            ->method('getLogger')
            ->willReturn($this->logger);

        // Create a concrete implementation for testing
        $this->instrumentation = new class($this->loggerInstrumentation) extends AbstractGatewayInstrumentation {
            public const NAME = 'test.gateway';
        };
    }

    public function testStartLogsGatewayRequestData(): void
    {
        $request = $this->createMock(GatewayRequest::class);
        $requestData = [
            'id' => '123',
            'action' => 'test',
        ];
        $request->method('data')->willReturn($requestData);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('test.gateway', $requestData);

        $this->instrumentation->start($request);
    }

    public function testSuccessLogsGatewayResponseData(): void
    {
        $response = $this->createMock(GatewayResponse::class);
        $responseData = [
            'result' => 'success',
            'data' => [
                'id' => '123',
            ],
        ];
        $response->method('data')->willReturn($responseData);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('test.gateway.success', $responseData);

        $this->instrumentation->success($response);
    }

    public function testErrorLogsGatewayRequestDataWithReason(): void
    {
        $request = $this->createMock(GatewayRequest::class);
        $requestData = [
            'id' => '123',
            'action' => 'test',
        ];
        $request->method('data')->willReturn($requestData);

        $reason = 'Validation failed';

        $expectedData = array_merge($requestData, [
            ' reason' => $reason,
        ]);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('test.gateway.error', $expectedData);

        $this->instrumentation->error($request, $reason);
    }

    public function testConstructorSetsLoggerFromInstrumentation(): void
    {
        // Test that the logger is properly retrieved from LoggerInstrumentation
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLoggerInstrumentation = $this->createMock(LoggerInstrumentation::class);
        $mockLoggerInstrumentation
            ->expects($this->once())
            ->method('getLogger')
            ->willReturn($mockLogger);

        new class($mockLoggerInstrumentation) extends AbstractGatewayInstrumentation {
            public const NAME = 'constructor.test';
        };

        // The expectation above verifies getLogger was called once
    }

    public function testInstrumentationImplementsGatewayInstrumentationInterface(): void
    {
        $this->assertInstanceOf(
            \App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation::class,
            $this->instrumentation
        );
    }

    public function testMultipleImplementationsCanHaveDifferentNames(): void
    {
        $instrumentation1 = new class($this->loggerInstrumentation) extends AbstractGatewayInstrumentation {
            public const NAME = 'gateway.one';
        };

        $instrumentation2 = new class($this->loggerInstrumentation) extends AbstractGatewayInstrumentation {
            public const NAME = 'gateway.two';
        };

        $request = $this->createMock(GatewayRequest::class);
        $request->method('data')->willReturn([]);

        $callCount = 0;
        $expectedCalls = [
            ['gateway.one', []],
            ['gateway.two', []],
        ];

        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message, $context) use (&$callCount, $expectedCalls) {
                $this->assertEquals($expectedCalls[$callCount][0], $message);
                $this->assertEquals($expectedCalls[$callCount][1], $context);
                ++$callCount;
            });

        $instrumentation1->start($request);
        $instrumentation2->start($request);
    }

    public function testErrorIncludesAllRequestDataWithReason(): void
    {
        $request = $this->createMock(GatewayRequest::class);
        $complexRequestData = [
            'id' => '123',
            'nested' => [
                'field1' => 'value1',
                'field2' => 'value2',
            ],
            'array' => [1, 2, 3],
        ];
        $request->method('data')->willReturn($complexRequestData);

        $reason = 'Complex error occurred';

        // The spread operator should merge all data correctly
        $expectedData = [
            ...$complexRequestData, ...[
                'reason' => $reason,
            ]];

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'test.gateway.error',
                $this->callback(fn ($data) => '123' === $data['id']
                    && $data['nested'] === [
                        'field1' => 'value1',
                        'field2' => 'value2',
                    ]
                    && $data['array'] === [1, 2, 3]
                    && $data[' reason'] === $reason)
            );

        $this->instrumentation->error($request, $reason);
    }
}
