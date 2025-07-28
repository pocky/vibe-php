<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Article\PublishArticle;

use App\Blog\Domain\Article\ArticlePublisher;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private ArticlePublisher $publisher,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $articleId = new ArticleId($command->articleId);

        // Execute domain operation
        $article = ($this->publisher)($articleId);

        // Dispatch domain events
        foreach ($article->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
