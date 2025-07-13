<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Content, Title};
use App\BlogContext\Domain\UpdateArticle\UpdaterInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private UpdaterInterface $updater,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): \App\BlogContext\Domain\UpdateArticle\DataPersister\Article
    {
        // Transform command to value objects
        $articleId = new ArticleId($command->articleId);
        $title = new Title($command->title);
        $content = new Content($command->content);

        // Execute domain operation
        $updatedArticle = ($this->updater)($articleId, $title, $content);

        // Dispatch domain events via EventBus
        foreach ($updatedArticle->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }

        return $updatedArticle;
    }
}
