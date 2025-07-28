<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Article\UpdateArticle;

use App\Blog\Domain\Article\ArticleUpdater;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private ArticleUpdater $updater,
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
        $article = ($this->updater)(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
        );

        // Dispatch domain events
        foreach ($article->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
