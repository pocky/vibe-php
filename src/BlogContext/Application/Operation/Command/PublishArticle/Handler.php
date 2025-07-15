<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\PublishArticle;

use App\BlogContext\Domain\PublishArticle\PublisherInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class Handler
{
    public function __construct(
        private PublisherInterface $publisher,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): \App\BlogContext\Domain\PublishArticle\DataPersister\Article
    {
        // Execute domain operation using the ArticleId from command
        $publishedArticle = ($this->publisher)($command->articleId);

        // Dispatch domain events via EventBus
        foreach ($publishedArticle->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }

        return $publishedArticle;
    }
}
