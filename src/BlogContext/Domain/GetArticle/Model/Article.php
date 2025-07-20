<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticle\Model;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;

/**
 * Represents article data during retrieval.
 * This is a data transfer object specific to the GetArticle operation.
 */
final readonly class Article
{
    public function __construct(
        public ArticleId $id,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
        public string $authorId,
        public Timestamps $timestamps,
        public \DateTimeImmutable|null $publishedAt = null,
    ) {
    }

    public static function fromReadModel(ArticleReadModel $readModel): self
    {
        return new self(
            id: $readModel->id,
            title: $readModel->title,
            content: $readModel->content,
            slug: $readModel->slug,
            status: $readModel->status,
            authorId: $readModel->authorId,
            timestamps: $readModel->timestamps,
            publishedAt: $readModel->publishedAt,
        );
    }
}
