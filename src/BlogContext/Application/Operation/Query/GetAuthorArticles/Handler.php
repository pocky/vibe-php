<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthorArticles;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

final readonly class Handler
{
    public function __construct(
        private AuthorRepositoryInterface $authorRepository,
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        $authorId = new AuthorId($query->authorId);

        // Check if author exists
        if (!$this->authorRepository->existsById($authorId)) {
            throw new \RuntimeException(sprintf('Author with ID "%s" not found', $query->authorId));
        }

        // Calculate offset from page and limit
        $offset = ($query->page - 1) * $query->limit;

        // Fetch articles for this author
        $articlesData = $this->articleRepository->findByAuthorId($authorId, $query->limit, $offset);

        // Get total count
        $total = $this->articleRepository->countByAuthorId($authorId);

        // Transform to view models
        $articleViews = array_map(
            fn ($article) => new ArticleView(
                id: $article['id'],
                title: $article['title'],
                slug: $article['slug'],
                status: $article['status'],
                publishedAt: null !== $article['publishedAt']
                    ? new \DateTimeImmutable($article['publishedAt'])
                    : null
            ),
            $articlesData
        );

        return new View(
            authorId: $query->authorId,
            articles: $articleViews,
            total: $total,
            page: $query->page,
            limit: $query->limit
        );
    }
}
