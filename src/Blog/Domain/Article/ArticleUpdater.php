<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\ValueObject\Slug;

final readonly class ArticleUpdater
{
    public function __construct(
        private ArticleWriteRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(
        ArticleId $articleId,
        Title $title,
        Content $content,
        Slug $slug,
    ): Article {
        // Retrieve the article from repository
        $article = $this->articleRepository->findById($articleId);

        if (!$article instanceof Article) {
            throw new \RuntimeException(sprintf('Article not found: %s', $articleId->getValue()));
        }

        // If slug is changing, ensure it's unique
        if (!$article->slug()->equals($slug)) {
            $existingArticle = $this->articleRepository->findBySlug($slug);
            if ($existingArticle instanceof Article && !$existingArticle->id()->equals($articleId)) {
                throw new \DomainException(sprintf('Slug already exists: %s', $slug->getValue()));
            }
        }

        // Update the article using the rich domain model
        $article->update($title, $content, $slug);

        // Persist changes
        $this->articleRepository->update($article);

        return $article;
    }
}
