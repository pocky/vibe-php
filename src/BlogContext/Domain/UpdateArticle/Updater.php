<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle;

use App\BlogContext\Domain\Shared\Model\Article as SharedArticle;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\BlogContext\Domain\UpdateArticle\DataPersister\Article;
use App\BlogContext\Domain\UpdateArticle\Exception\{ArticleNotFound, ArticleSlugAlreadyExists, PublishedArticleRequiresApproval};

final readonly class Updater implements UpdaterInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    #[\Override]
    public function __invoke(ArticleId $articleId, Title $title, Content $content, Slug $slug, ArticleStatus $status): Article
    {
        // Find existing article
        $existingArticle = $this->repository->findById($articleId);
        if (!$existingArticle instanceof SharedArticle) {
            throw new ArticleNotFound($articleId);
        }

        // Business rule: Published articles require editor approval for major changes
        if ($existingArticle->getStatus()->isPublished()) {
            throw new PublishedArticleRequiresApproval($articleId);
        }

        // Check if new slug conflicts with another article
        if (!$slug->equals($existingArticle->getSlug()) && $this->repository->existsBySlug($slug)) {
            throw new ArticleSlugAlreadyExists($slug);
        }

        // Create updated article
        $updatedArticle = new Article(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            createdAt: $existingArticle->getCreatedAt(),
            updatedAt: new \DateTimeImmutable(),
            originalTitle: $existingArticle->getTitle(),
            originalContent: $existingArticle->getContent(),
        );

        // Persist
        $this->repository->save($updatedArticle);

        return $updatedArticle;
    }
}
