<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\MessageBus;

use App\Shared\Infrastructure\MessageBus\LoggerMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LoggerMiddlewareTest extends TestCase
{
    private LoggerInterface $logger;
    private NormalizerInterface $normalizer;
    private LoggerMiddleware $middleware;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->middleware = new LoggerMiddleware($this->logger, $this->normalizer);
    }

    public function testHandleLogsReceivedAndHandledMessages(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message);
        $handledResult = 'handled-result';

        $processedEnvelope = $envelope->with(new HandledStamp($handledResult, 'handler'));

        $this->normalizer->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnMap([
                [
                    $message, null, [], [
                        'normalized' => 'message',
                    ]],
                [
                    $handledResult, null, [], [
                        'normalized' => 'result',
                    ]],
            ]);

        $expectedReceivedLog = json_encode([
            'type' => 'received',
            'content' => [
                'normalized' => 'message',
            ],
            'messageType' => \stdClass::class,
        ]);

        $expectedHandledLog = json_encode([
            'type' => 'handled',
            'content' => [
                'normalized' => 'result',
            ],
            'messageType' => \stdClass::class,
        ]);

        $actualLogs = [];
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message) use (&$actualLogs) {
                $actualLogs[] = $message;
            });

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(StackMiddleware::class);

        $stack->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware);

        $nextMiddleware->expects($this->once())
            ->method('handle')
            ->with($envelope, $stack)
            ->willReturn($processedEnvelope);

        $result = $this->middleware->handle($envelope, $stack);

        $this->assertSame($processedEnvelope, $result);
        $this->assertCount(2, $actualLogs);
        $this->assertSame('data : ' . $expectedReceivedLog, $actualLogs[0]);
        $this->assertSame('data : ' . $expectedHandledLog, $actualLogs[1]);
    }

    public function testHandleWithoutHandledStamp(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message);

        $this->normalizer->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnMap([
                [
                    $message, null, [], [
                        'normalized' => 'message',
                    ]],
                [null, null, [], null],
            ]);

        $expectedReceivedLog = json_encode([
            'type' => 'received',
            'content' => [
                'normalized' => 'message',
            ],
            'messageType' => \stdClass::class,
        ]);

        $expectedHandledLog = json_encode([
            'type' => 'handled',
            'content' => null,
            'messageType' => \stdClass::class,
        ]);

        $actualLogs = [];
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message) use (&$actualLogs) {
                $actualLogs[] = $message;
            });

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(StackMiddleware::class);

        $stack->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware);

        $nextMiddleware->expects($this->once())
            ->method('handle')
            ->with($envelope, $stack)
            ->willReturn($envelope);

        $result = $this->middleware->handle($envelope, $stack);

        $this->assertSame($envelope, $result);
        $this->assertCount(2, $actualLogs);
        $this->assertSame('data : ' . $expectedReceivedLog, $actualLogs[0]);
        $this->assertSame('data : ' . $expectedHandledLog, $actualLogs[1]);
    }

    public function testHandlePassesEnvelopeThroughMiddlewareChain(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message);
        $modifiedEnvelope = $envelope->with(new HandledStamp('result', 'handler'));

        $this->normalizer->method('normalize')->willReturn([]);
        $this->logger->method('info');

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(StackMiddleware::class);

        $stack->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware);

        $nextMiddleware->expects($this->once())
            ->method('handle')
            ->with($envelope, $stack)
            ->willReturn($modifiedEnvelope);

        $result = $this->middleware->handle($envelope, $stack);

        $this->assertSame($modifiedEnvelope, $result);
    }
}
