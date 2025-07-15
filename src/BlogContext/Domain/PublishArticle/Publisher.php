<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle;

use App\BlogContext\Domain\PublishArticle\DataPersister\Article;
use App\BlogContext\Domain\PublishArticle\Exception\{ArticleAlreadyPublished, ArticleNotFound, ArticleNotReady};
use App\BlogContext\Domain\Shared\Model\Article as SharedArticle;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus};

final readonly class Publisher implements PublisherInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    #[\Override]
    public function __invoke(ArticleId $articleId): Article
    {
        // Find existing article
        $existingArticle = $this->repository->findById($articleId);
        if (!$existingArticle instanceof SharedArticle) {
            throw new ArticleNotFound($articleId);
        }

        // Business rule: Only draft articles can be published
        if ($existingArticle->getStatus()->isPublished()) {
            throw new ArticleAlreadyPublished($articleId);
        }

        if ($existingArticle->getStatus()->isArchived()) {
            throw new ArticleNotReady($articleId, 'Cannot publish archived article');
        }

        // Business rule: Article must have content for publication
        if (10 > strlen(trim($existingArticle->getContent()->getValue()))) {
            throw new ArticleNotReady($articleId, 'Article content is too short for publication');
        }

        // Create published article
        $publishedArticle = new Article(
            id: $articleId,
            title: $existingArticle->getTitle(),
            content: $existingArticle->getContent(),
            slug: $existingArticle->getSlug(),
            status: ArticleStatus::PUBLISHED,
            createdAt: $existingArticle->getCreatedAt(),
            publishedAt: new \DateTimeImmutable(),
        );

        // Persist
        $this->repository->save($publishedArticle);

        return $publishedArticle;
    }
}
