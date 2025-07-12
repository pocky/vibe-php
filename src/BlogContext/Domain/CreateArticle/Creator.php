<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};

final readonly class Creator implements CreatorInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        ArticleId $articleId,
        Title $title,
        Content $content,
        Slug $slug,
        ArticleStatus $status,
        \DateTimeImmutable $createdAt,
    ): Article {
        // Check if article with this slug already exists
        if ($this->repository->existsBySlug($slug)) {
            throw new ArticleAlreadyExists($slug);
        }

        // Create article with provided values
        $article = new Article(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            createdAt: $createdAt,
        );

        // Persist
        $this->repository->save($article);

        // Return article with unreleased events for Application layer to handle
        return $article;
    }
}
