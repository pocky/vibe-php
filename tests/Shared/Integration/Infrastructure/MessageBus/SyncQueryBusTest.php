<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\MessageBus;

use App\Shared\Infrastructure\MessageBus\SyncQueryBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class SyncQueryBusTest extends TestCase
{
    public function testInvokeWithObjectQuery(): void
    {
        $query = new \stdClass();
        $expectedResult = 'query-result';

        $handler = (fn (): string => $expectedResult);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncQueryBus = new SyncQueryBus($messageBus);
        $result = $syncQueryBus($query);

        $this->assertSame($expectedResult, $result);
    }

    public function testInvokeWithEnvelopeQuery(): void
    {
        $query = new \stdClass();
        $envelope = new Envelope($query);
        $expectedResult = 'query-result';

        $handler = (fn (): string => $expectedResult);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncQueryBus = new SyncQueryBus($messageBus);
        $result = $syncQueryBus($envelope);

        $this->assertSame($expectedResult, $result);
    }

    public function testInvokeThrowsExceptionWhenHandlerFails(): void
    {
        $query = new \stdClass();
        $runtimeException = new \RuntimeException('Handler failed');

        $handler = function () use ($runtimeException): never {
            throw $runtimeException;
        };

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncQueryBus = new SyncQueryBus($messageBus);

        $this->expectException(HandlerFailedException::class);
        $syncQueryBus($query);
    }

    public function testInvokeWithNoHandler(): void
    {
        $query = new \stdClass();

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([])),
        ]);

        $syncQueryBus = new SyncQueryBus($messageBus);

        $this->expectException(\Symfony\Component\Messenger\Exception\NoHandlerForMessageException::class);
        $syncQueryBus($query);
    }

    public function testInvokeWithMultipleHandlers(): void
    {
        $query = new \stdClass();
        $expectedResult1 = 'result-1';
        $expectedResult2 = 'result-2';

        $handler1 = (fn (): string => $expectedResult1);

        $handler2 = (fn (): string => $expectedResult2);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler1, $handler2],
            ])),
        ]);

        $syncQueryBus = new SyncQueryBus($messageBus);
        $result = $syncQueryBus($query);

        $this->assertSame($expectedResult1, $result);
    }

    public function testInvokeReturnsQueryViewData(): void
    {
        $query = new \stdClass();
        $viewData = [
            'id' => 1,
            'name' => 'Test Query',
            'data' => [
                'key' => 'value',
            ],
        ];

        $handler = (fn (): array => $viewData);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                \stdClass::class => [$handler],
            ])),
        ]);

        $syncQueryBus = new SyncQueryBus($messageBus);
        $result = $syncQueryBus($query);

        $this->assertSame($viewData, $result);
    }
}
