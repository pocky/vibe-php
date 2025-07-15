<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\SubmitForReview;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\SubmitForReview\DataProvider\ArticleForReview;
use App\BlogContext\Domain\SubmitForReview\Submitter;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private EventBusInterface $eventBus,
        private Submitter $submitter,
    ) {
    }

    public function __invoke(Command $command): void
    {
        $articleId = new ArticleId($command->articleId);
        $articleData = $this->articleRepository->findById($articleId);

        if (!$articleData instanceof \App\BlogContext\Domain\Shared\Repository\ArticleData) {
            throw new \RuntimeException('Article not found');
        }

        $articleForReview = new ArticleForReview(
            articleId: $articleData->id,
            title: $articleData->title,
            status: $articleData->status,
            submittedAt: new \DateTimeImmutable(),
        );

        try {
            $article = ($this->submitter)($articleForReview);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        // Save the updated article status
        $this->articleRepository->save($article);

        // Dispatch events
        foreach ($article->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
