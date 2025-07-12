<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final readonly class Command
{
    public function __construct(
        public ArticleId $articleId,
        public string $title,
        public string $content,
        public string $slug,
        public string $status,
        public \DateTimeImmutable $createdAt,
        public string|null $authorId = null,
    ) {
    }
}
