<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateAuthor;

use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Domain\UpdateAuthor\Model\Author;

interface UpdaterInterface
{
    public function __invoke(
        AuthorId $authorId,
        AuthorName $name,
        AuthorEmail $email,
        AuthorBio $bio,
        \DateTimeImmutable $updatedAt,
    ): Author;
}
