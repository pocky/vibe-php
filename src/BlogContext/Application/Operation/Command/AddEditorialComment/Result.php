<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\AddEditorialComment;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $articleId,
        public string $reviewerId,
        public string $comment,
        public \DateTimeImmutable $createdAt,
        public string|null $selectedText = null,
        public int|null $positionStart = null,
        public int|null $positionEnd = null,
    ) {
    }
}
