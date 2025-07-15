<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\AutoSaveArticle;

use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\UpdateArticle\UpdaterInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class Handler
{
    public function __construct(
        private UpdaterInterface $updater,
        private EventBusInterface $eventBus,
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(Command $command): \App\BlogContext\Domain\UpdateArticle\DataPersister\Article
    {
        // Get value objects from command
        $articleId = $command->articleId;
        $title = $command->title;
        $content = $command->content;

        // Get existing article data to preserve slug and status
        $existingArticle = $this->articleRepository->findById($articleId);
        if (!$existingArticle instanceof Article) {
            throw new \RuntimeException('Article not found');
        }

        // Execute domain operation - AutoSave uses the same business logic as Update
        $updatedArticle = ($this->updater)($articleId, $title, $content, $existingArticle->getSlug(), $existingArticle->getStatus());

        // Dispatch domain events via EventBus
        foreach ($updatedArticle->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }

        return $updatedArticle;
    }
}
