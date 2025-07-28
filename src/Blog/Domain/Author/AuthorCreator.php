<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;

final readonly class AuthorCreator
{
    public function __construct(
        private AuthorWriteRepositoryInterface $authorRepository,
    ) {
    }

    public function __invoke(
        AuthorId $authorId,
        AuthorName $authorName,
        AuthorEmail $authorEmail,
        AuthorBio $authorBio,
    ): Author {
        // Check if author with this email already exists
        $existingAuthor = $this->authorRepository->findByEmail($authorEmail);
        if ($existingAuthor instanceof Author) {
            throw new \DomainException(sprintf('Author with email "%s" already exists', $authorEmail->getValue()));
        }

        // Create author using aggregate factory method with provided ID
        $author = Author::create($authorId, $authorName, $authorEmail, $authorBio);

        // Persist the author
        $this->authorRepository->add($author);

        return $author;
    }
}
