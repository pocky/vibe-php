<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Repository;

use App\Blog\Application\Shared\ReadModel\AuthorReadModel;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;

/**
 * Repository interface for author read operations.
 * Used by query handlers and read operations.
 */
interface AuthorReadRepositoryInterface
{
    /**
     * Find an author by ID.
     */
    public function findById(AuthorId $authorId): AuthorReadModel|null;

    /**
     * Find an author by email.
     */
    public function findByEmail(AuthorEmail $authorEmail): AuthorReadModel|null;

    /**
     * Check if an author exists by ID.
     */
    public function existsById(AuthorId $authorId): bool;

    /**
     * Check if an author exists by email.
     */
    public function existsByEmail(AuthorEmail $authorEmail): bool;

    /**
     * Find authors with pagination.
     *
     * @return array{authors: AuthorReadModel[], total: int}
     */
    public function findAllPaginated(int $limit, int $offset): array;

    /**
     * Search authors by name pattern.
     *
     * @return AuthorReadModel[]
     */
    public function searchByName(string $namePattern, int $limit = 10): array;
}
