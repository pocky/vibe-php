<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateAuthor;

use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Domain\UpdateAuthor\Exception\AuthorNotFound;
use App\BlogContext\Domain\UpdateAuthor\Model\Author;

final readonly class Updater
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        AuthorId $authorId,
        AuthorName $name,
        AuthorEmail $email,
        AuthorBio $bio,
        \DateTimeImmutable $updatedAt
    ): Author {
        // Find existing author
        $existingAuthor = $this->repository->findById($authorId);
        if (!$existingAuthor instanceof \App\BlogContext\Domain\CreateAuthor\Model\Author) {
            throw AuthorNotFound::withId($authorId);
        }

        // Check if email is being changed and already exists for another author
        $authorWithEmail = $this->repository->findByEmail($email);
        if ($authorWithEmail instanceof \App\BlogContext\Domain\CreateAuthor\Model\Author && $authorWithEmail->id()->getValue() !== $authorId->getValue()) {
            throw new \InvalidArgumentException(sprintf('Email "%s" is already used by another author', $email->getValue()));
        }

        // Create updated author model
        return Author::update(
            $authorId,
            $name,
            $email,
            $bio,
            $existingAuthor->createdAt(),
            $updatedAt
        );
    }
}
