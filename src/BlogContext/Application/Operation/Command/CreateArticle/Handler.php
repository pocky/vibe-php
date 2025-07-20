<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

use App\BlogContext\Domain\CreateArticle\CreatorInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // NOTE: This handler expects the ArticleId and Slug to be provided in the command
        // If you need to generate them, use the Gateway Processor instead

        // Transform command data to value objects
        $articleId = new ArticleId($command->articleId);
        $title = new Title($command->title);
        $content = new Content($command->content);
        $slug = new Slug($command->slug); // Now required

        // Execute domain operation
        $articleData = ($this->creator)(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $command->authorId,
        );

        // Dispatch domain events
        foreach ($articleData->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
