<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

use App\BlogContext\Domain\CreateArticle\CreatorInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleStatus, Content, Slug, Title};
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Convert string values to domain value objects
        $title = new Title($command->title);
        $content = new Content($command->content);
        $slug = new Slug($command->slug);
        $status = ArticleStatus::fromString($command->status);

        // Call domain creator to get article with domain events
        $article = ($this->creator)(
            articleId: $command->articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            createdAt: $command->createdAt,
        );

        // Dispatch domain events via EventBus
        foreach ($article->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
