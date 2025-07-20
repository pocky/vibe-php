<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Builder;

use App\BlogContext\Domain\CreateArticle\Model\Article;
use App\BlogContext\Domain\Shared\Builder\ArticleBuilderInterface;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;

/**
 * Builder for creating Article models in the CreateArticle context.
 */
final class ArticleBuilder implements ArticleBuilderInterface
{
    public function fromReadModel(ArticleReadModel $readModel): Article
    {
        return new Article(
            id: $readModel->id,
            title: $readModel->title,
            content: $readModel->content,
            slug: $readModel->slug,
            status: $readModel->status,
            authorId: $readModel->authorId,
            timestamps: $readModel->timestamps,
            events: []
        );
    }

    public function fromArray(array $data): Article
    {
        return new Article(
            id: new ArticleId($data['id']),
            title: new Title($data['title']),
            content: new Content($data['content']),
            slug: new Slug($data['slug']),
            status: ArticleStatus::from($data['status'] ?? 'draft'),
            authorId: $data['authorId'],
            timestamps: isset($data['createdAt']) && isset($data['updatedAt'])
                ? new Timestamps(
                    new \DateTimeImmutable($data['createdAt']),
                    new \DateTimeImmutable($data['updatedAt'])
                )
                : Timestamps::create(),
            events: []
        );
    }

    /**
     * Create a new article for creation.
     */
    public function createNew(
        ArticleId $id,
        Title $title,
        Content $content,
        Slug $slug,
        string $authorId
    ): Article {
        return Article::create(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $authorId
        );
    }
}
