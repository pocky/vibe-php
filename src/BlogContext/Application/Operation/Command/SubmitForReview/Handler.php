<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\SubmitForReview;

use App\BlogContext\Domain\Shared\Model\Article;
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
        $article = $this->articleRepository->findById($articleId);

        if (!$article instanceof Article) {
            throw new \RuntimeException('Article not found');
        }

        $articleForReview = new ArticleForReview(
            articleId: $article->getId(),
            title: $article->getTitle(),
            status: $article->getStatus(),
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
