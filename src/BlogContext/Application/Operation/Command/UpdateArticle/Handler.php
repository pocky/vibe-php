<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\UpdateArticle\UpdaterInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private UpdaterInterface $updater,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $articleId = new ArticleId($command->articleId);
        $title = null !== $command->title ? new Title($command->title) : null;
        $content = null !== $command->content ? new Content($command->content) : null;
        $slug = null !== $command->slug ? new Slug($command->slug) : null;

        // Execute domain operation
        $updateData = ($this->updater)(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
        );

        // Dispatch domain events
        foreach ($updateData->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
