<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteAuthor;

use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

interface DeletorInterface
{
    public function __invoke(
        AuthorId $authorId,
        \DateTimeImmutable $deletedAt,
    ): void;
}
