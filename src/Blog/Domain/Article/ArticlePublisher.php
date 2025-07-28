<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article;

use App\Blog\Domain\Article\Shared\Exception\ArticleNotFound;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;

final readonly class ArticlePublisher
{
    public function __construct(
        private ArticleWriteRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(ArticleId $articleId): Article
    {
        // Retrieve the article from repository
        $article = $this->articleRepository->findById($articleId);

        if (!$article instanceof Article) {
            throw new ArticleNotFound($articleId);
        }

        // Publish the article using the rich domain model
        $article->publish();

        // Persist changes
        $this->articleRepository->update($article);

        return $article;
    }
}
