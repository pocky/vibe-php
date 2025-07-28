<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;

final readonly class AuthorUpdater
{
    public function __construct(
        private AuthorWriteRepositoryInterface $authorRepository,
    ) {
    }

    public function __invoke(
        AuthorId $authorId,
        AuthorName|null $name = null,
        AuthorEmail|null $email = null,
        AuthorBio|null $bio = null,
    ): Author {
        // Find existing author
        $author = $this->authorRepository->findById($authorId);
        if (!$author instanceof Author) {
            throw new \RuntimeException(sprintf('Author not found: %s', $authorId->getValue()));
        }

        // Check if email is being changed and already exists for another author
        if ($email instanceof AuthorEmail && !$author->getEmail()->equals($email)) {
            $authorWithEmail = $this->authorRepository->findByEmail($email);
            if ($authorWithEmail instanceof Author && !$authorWithEmail->getId()->equals($authorId)) {
                throw new \InvalidArgumentException(sprintf('Email "%s" is already used by another author', $email->getValue()));
            }
        }

        // Update the author using aggregate method
        $author->update($name, $email, $bio);

        // Persist changes
        $this->authorRepository->update($author);

        return $author;
    }
}
