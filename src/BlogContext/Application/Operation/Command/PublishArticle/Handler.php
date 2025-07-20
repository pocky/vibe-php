<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\PublishArticle;

use App\BlogContext\Domain\PublishArticle\PublisherInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private PublisherInterface $publisher,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $articleId = new ArticleId($command->articleId);
        $publishAt = null !== $command->publishAt ? new \DateTimeImmutable($command->publishAt) : null;

        // Execute domain operation
        $publishData = ($this->publisher)(
            articleId: $articleId,
            publishAt: $publishAt,
        );

        // Dispatch domain events
        foreach ($publishData->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
