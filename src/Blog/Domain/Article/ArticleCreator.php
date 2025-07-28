<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article;

use App\Blog\Domain\Article\Shared\Exception\ArticleAlreadyExists;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Shared\ValueObject\Slug;

final readonly class ArticleCreator
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
        AuthorId $authorId,
    ): Article {
        // Verify slug uniqueness
        if ($this->articleRepository->findBySlug($slug) instanceof Article) {
            throw new ArticleAlreadyExists($articleId->getValue());
        }

        // Create the article using the rich domain model
        $article = Article::create(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $authorId,
        );

        // Persist the article
        $this->articleRepository->add($article);

        // Return the article with its events
        return $article;
    }
}
