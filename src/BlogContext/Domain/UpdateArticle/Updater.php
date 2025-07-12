<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle;

use App\BlogContext\Domain\Shared\Repository\{ArticleData, ArticleRepositoryInterface};
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Content, Slug, Title};
use App\BlogContext\Domain\UpdateArticle\DataPersister\Article;
use App\BlogContext\Domain\UpdateArticle\Exception\{ArticleNotFound, ArticleSlugAlreadyExists, PublishedArticleRequiresApproval};
use App\Shared\Infrastructure\Slugger\SluggerInterface;

final readonly class Updater
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
        private SluggerInterface $slugger,
    ) {
    }

    public function __invoke(ArticleId $articleId, Title $title, Content $content): Article
    {
        // Find existing article
        $existingArticleData = $this->repository->findById($articleId);
        if (!$existingArticleData instanceof ArticleData) {
            throw new ArticleNotFound($articleId);
        }

        // Business rule: Published articles require editor approval for major changes
        if ($existingArticleData->status->isPublished()) {
            throw new PublishedArticleRequiresApproval($articleId);
        }

        // Apply business rules: generate new slug from title if title changed
        $slug = $title->equals($existingArticleData->title)
            ? $existingArticleData->slug
            : new Slug($this->slugger->slugify($title->getValue()));

        // Check if new slug conflicts with another article
        if (!$slug->equals($existingArticleData->slug) && $this->repository->existsBySlug($slug)) {
            throw new ArticleSlugAlreadyExists($slug);
        }

        // Create updated article
        $updatedArticle = new Article(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $existingArticleData->status,
            createdAt: $existingArticleData->createdAt,
            updatedAt: new \DateTimeImmutable(),
            originalTitle: $existingArticleData->title,
            originalContent: $existingArticleData->content,
        );

        // Persist
        $this->repository->save($updatedArticle);

        return $updatedArticle;
    }
}
