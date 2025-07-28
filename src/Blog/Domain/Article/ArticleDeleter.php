<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article;

use App\Blog\Domain\Article\Shared\Exception\ArticleNotFound;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;

final readonly class ArticleDeleter
{
    public function __construct(
        private ArticleWriteRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(ArticleId $articleId): void
    {
        // Retrieve the article from repository
        $article = $this->articleRepository->findById($articleId);

        if (!$article instanceof Article) {
            throw new ArticleNotFound($articleId);
        }

        // Physically remove the article
        $this->articleRepository->remove($article);
    }
}
