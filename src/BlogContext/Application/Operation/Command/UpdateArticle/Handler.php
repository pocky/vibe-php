<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Domain\UpdateArticle\UpdaterInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class Handler
{
    public function __construct(
        private UpdaterInterface $updater,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): \App\BlogContext\Domain\UpdateArticle\DataPersister\Article
    {
        // Execute domain operation using value objects from command
        $updatedArticle = ($this->updater)($command->articleId, $command->title, $command->content, $command->slug, $command->status);

        // Dispatch domain events via EventBus
        foreach ($updatedArticle->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }

        return $updatedArticle;
    }
}
