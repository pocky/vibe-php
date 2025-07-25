<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateAuthor;

use App\BlogContext\Domain\CreateAuthor\Exception\AuthorAlreadyExists;
use App\BlogContext\Domain\CreateAuthor\Model\Author;
use App\BlogContext\Domain\Shared\Generator\AuthorIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;

final readonly class Creator
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
        private AuthorIdGeneratorInterface $idGenerator,
    ) {
    }

    public function __invoke(
        AuthorName $name,
        AuthorEmail $email,
        AuthorBio $bio,
        \DateTimeImmutable $createdAt
    ): Author {
        // Check if author with this email already exists
        $existingAuthor = $this->repository->findByEmail($email);
        if ($existingAuthor instanceof Author) {
            throw AuthorAlreadyExists::withEmail($email);
        }

        // Generate new ID
        $id = $this->idGenerator->nextIdentity();

        // Create and return new author
        return Author::create(
            $id,
            $name,
            $email,
            $bio,
            $createdAt
        );
    }
}
