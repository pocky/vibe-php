<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\DeleteArticle;

use App\BlogContext\Domain\DeleteArticle\DeleterInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private DeleterInterface $deleter,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $articleId = new ArticleId($command->articleId);

        // Execute domain operation
        $deleteData = ($this->deleter)($articleId, $command->deletedBy);

        // Dispatch domain events
        foreach ($deleteData->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
