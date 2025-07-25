<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateAuthor;

use App\BlogContext\Domain\CreateAuthor\Model\Author;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;

interface CreatorInterface
{
    public function __invoke(
        AuthorId $authorId,
        AuthorName $name,
        AuthorEmail $email,
        AuthorBio $bio,
        \DateTimeImmutable $createdAt,
    ): Author;
}
