<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\MessageBus;

use App\Shared\Infrastructure\MessageBus\SyncCommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class SyncCommandBusTest extends TestCase
{
    public function testInvokeWithObjectCommand(): void
    {
        $command = new \stdClass();
        $expectedResult = 'command-result';

        $handler = (fn (): string => $expectedResult);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncCommandBus = new SyncCommandBus($messageBus);
        $result = $syncCommandBus($command);

        $this->assertSame($expectedResult, $result);
    }

    public function testInvokeWithEnvelopeCommand(): void
    {
        $command = new \stdClass();
        $envelope = new Envelope($command);
        $expectedResult = 'command-result';

        $handler = (fn (): string => $expectedResult);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncCommandBus = new SyncCommandBus($messageBus);
        $result = $syncCommandBus($envelope);

        $this->assertSame($expectedResult, $result);
    }

    public function testInvokeThrowsExceptionWhenHandlerFails(): void
    {
        $command = new \stdClass();
        $runtimeException = new \RuntimeException('Handler failed');

        $handler = function () use ($runtimeException): never {
            throw $runtimeException;
        };

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncCommandBus = new SyncCommandBus($messageBus);

        $this->expectException(HandlerFailedException::class);
        $syncCommandBus($command);
    }

    public function testInvokeWithNoHandler(): void
    {
        $command = new \stdClass();

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([])),
        ]);

        $syncCommandBus = new SyncCommandBus($messageBus);

        $this->expectException(\Symfony\Component\Messenger\Exception\NoHandlerForMessageException::class);
        $syncCommandBus($command);
    }

    public function testInvokeWithMultipleHandlers(): void
    {
        $command = new \stdClass();
        $expectedResult1 = 'result-1';
        $expectedResult2 = 'result-2';

        $handler1 = (fn (): string => $expectedResult1);

        $handler2 = (fn (): string => $expectedResult2);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler1, $handler2],
            ])),
        ]);

        $syncCommandBus = new SyncCommandBus($messageBus);
        $result = $syncCommandBus($command);

        $this->assertSame($expectedResult1, $result);
    }
}
