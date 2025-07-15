<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

use App\BlogContext\Domain\CreateArticle\CreatorInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleStatus, Content, Slug, Title};
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class Handler
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): \App\BlogContext\Domain\CreateArticle\DataPersister\Article
    {
        // Transform command to value objects
        $title = new Title($command->title);
        $content = new Content($command->content);
        $slug = new Slug($command->slug);
        $status = ArticleStatus::from($command->status);

        // Execute domain operation
        $createdArticle = ($this->creator)(
            $command->articleId,
            $title,
            $content,
            $slug,
            $status,
            $command->createdAt,
        );

        // Dispatch domain events via EventBus
        foreach ($createdArticle->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }

        return $createdArticle;
    }
}
