<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Article\CreateArticle;

use App\Blog\Domain\Article\ArticleCreator;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private ArticleCreator $creator,
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
        $article = ($this->creator)(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: new AuthorId($command->authorId),
        );

        // Dispatch domain events
        foreach ($article->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
