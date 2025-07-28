<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\MessageBus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class SyncCommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(
        /** @phpstan-ignore-next-line */
        private readonly MessageBusInterface $bus,
    ) {
        $this->messageBus = $bus;
    }

    /**
     * @param Envelope|object $command
     */
    #[\Override]
    public function __invoke($command): mixed
    {
        return $this->handle($command);
    }
}
