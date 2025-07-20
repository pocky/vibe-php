<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticles\Model;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;

/**
 * Represents article data in a list.
 * This is a simplified DTO for list operations.
 */
final readonly class Article
{
    public function __construct(
        public ArticleId $id,
        public Title $title,
        public Slug $slug,
        public ArticleStatus $status,
        public string $authorId,
        public Timestamps $timestamps,
        public \DateTimeImmutable|null $publishedAt = null,
        public string|null $excerpt = null,
    ) {
    }

    public static function fromReadModel(ArticleReadModel $readModel): self
    {
        return new self(
            id: $readModel->id,
            title: $readModel->title,
            slug: $readModel->slug,
            status: $readModel->status,
            authorId: $readModel->authorId,
            timestamps: $readModel->timestamps,
            publishedAt: $readModel->publishedAt,
            excerpt: self::createExcerpt($readModel->content),
        );
    }

    private static function createExcerpt(Content $content): string
    {
        $text = strip_tags($content->getValue());

        if (200 >= mb_strlen($text)) {
            return $text;
        }

        return mb_substr($text, 0, 197) . '...';
    }
}
