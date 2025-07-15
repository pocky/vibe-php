<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\ApproveArticle;

use App\BlogContext\Domain\ReviewArticle\DataProvider\ArticleReview;
use App\BlogContext\Domain\ReviewArticle\Reviewer;
use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ReviewDecision;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private EventBusInterface $eventBus,
        private Reviewer $reviewer,
    ) {
    }

    public function __invoke(Command $command): void
    {
        $articleId = new ArticleId($command->articleId);

        $article = $this->articleRepository->findById($articleId);
        if (!$article instanceof Article) {
            throw new \RuntimeException('Article not found');
        }

        $articleReview = new ArticleReview(
            articleId: $article->getId(),
            title: $article->getTitle(),
            status: $article->getStatus(),
            reviewerId: $command->reviewerId,
            decision: ReviewDecision::approve($command->reason),
            reviewedAt: new \DateTimeImmutable(),
        );

        $reviewedArticle = ($this->reviewer)($articleReview);

        $this->articleRepository->save($reviewedArticle);

        // Dispatch domain events
        $events = $reviewedArticle->releaseEvents();
        foreach ($events as $event) {
            ($this->eventBus)($event);
        }
    }
}
