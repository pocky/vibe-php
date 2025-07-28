<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Article\GetAuthorArticles;

use App\Blog\Domain\Article\Shared\Repository\ArticleReadRepositoryInterface;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Repository\AuthorReadRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private AuthorReadRepositoryInterface $authorRepository,
        private ArticleReadRepositoryInterface $articleReadRepository,
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
        $articlesData = $this->articleReadRepository->findByAuthorId($authorId, $query->limit, $offset);

        // Get total count
        $total = $this->articleReadRepository->countByAuthorId($authorId);

        // Transform to view models
        $articleViews = array_map(
            /** @param array{id: string, title: string, slug: string, status: string, publishedAt: string|null} $article */
            fn (array $article): ArticleView => new ArticleView(
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
