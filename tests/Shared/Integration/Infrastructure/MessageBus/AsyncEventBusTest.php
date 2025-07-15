<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\MessageBus;

use App\Shared\Infrastructure\MessageBus\AsyncEventBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncEventBusTest extends TestCase
{
    public function testInvokeWithObject(): void
    {
        $event = new \stdClass();
        $envelope = new Envelope($event);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn($envelope);

        $eventBus = new AsyncEventBus($messageBus);
        $result = $eventBus($event);

        $this->assertSame($envelope, $result);
    }

    public function testInvokeWithEnvelope(): void
    {
        $event = new \stdClass();
        $envelope = new Envelope($event);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($envelope)
            ->willReturn($envelope);

        $eventBus = new AsyncEventBus($messageBus);
        $result = $eventBus($envelope);

        $this->assertSame($envelope, $result);
    }

    public function testInvokeReturnsDispatchResult(): void
    {
        $event = new \stdClass();
        $envelope = new Envelope($event);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn($envelope);

        $eventBus = new AsyncEventBus($messageBus);
        $result = $eventBus($event);

        $this->assertSame($envelope, $result);
    }
}
