<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\MessageBus;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->logReceived($envelope);
        $envelope = $stack->next()->handle($envelope, $stack);
        $this->logHandled($envelope);

        return $envelope;
    }

    private function logReceived(Envelope $envelope): void
    {
        $result = [
            'type' => 'received',
            'content' => $this->normalizer->normalize($envelope->getMessage()),
            'messageType' => $envelope->getMessage()::class,
        ];

        $this->logger->info('data : ' . json_encode($result));
    }

    private function logHandled(Envelope $envelope): void
    {
        $result = [
            'type' => 'handled',
            'content' => $this->normalizer->normalize($envelope->last(HandledStamp::class)?->getResult()),
            'messageType' => $envelope->getMessage()::class,
        ];

        $this->logger->info('data : ' . json_encode($result));
    }
}
