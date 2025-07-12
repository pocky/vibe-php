<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle;

use App\BlogContext\Domain\PublishArticle\DataPersister\Article;
use App\BlogContext\Domain\PublishArticle\Exception\{ArticleAlreadyPublished, ArticleNotFound, ArticleNotReady};
use App\BlogContext\Domain\Shared\Repository\{ArticleData, ArticleRepositoryInterface};
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus};

final readonly class Publisher
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ArticleId $articleId): Article
    {
        // Find existing article
        $existingArticleData = $this->repository->findById($articleId);
        if (!$existingArticleData instanceof ArticleData) {
            throw new ArticleNotFound($articleId);
        }

        // Business rule: Only draft articles can be published
        if ($existingArticleData->status->isPublished()) {
            throw new ArticleAlreadyPublished($articleId);
        }

        if ($existingArticleData->status->isArchived()) {
            throw new ArticleNotReady($articleId, 'Cannot publish archived article');
        }

        // Business rule: Article must have content for publication
        if (10 > strlen(trim($existingArticleData->content->getValue()))) {
            throw new ArticleNotReady($articleId, 'Article content is too short for publication');
        }

        // Create published article
        $publishedArticle = new Article(
            id: $articleId,
            title: $existingArticleData->title,
            content: $existingArticleData->content,
            slug: $existingArticleData->slug,
            status: ArticleStatus::PUBLISHED,
            createdAt: $existingArticleData->createdAt,
            publishedAt: new \DateTimeImmutable(),
        );

        // Persist
        $this->repository->save($publishedArticle);

        return $publishedArticle;
    }
}
