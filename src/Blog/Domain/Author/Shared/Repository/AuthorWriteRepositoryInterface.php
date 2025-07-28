<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Repository;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;

/**
 * Write repository interface for Author aggregate.
 * Only contains methods for persisting and removing aggregates.
 * Read operations should use AuthorReadRepositoryInterface.
 */
interface AuthorWriteRepositoryInterface
{
    /**
     * Add a new author aggregate
     */
    public function add(Author $author): void;

    /**
     * Update an existing author aggregate
     */
    public function update(Author $author): void;

    /**
     * Remove an author aggregate
     */
    public function remove(Author $author): void;

    /**
     * Remove an author by ID
     */
    public function removeById(AuthorId $authorId): void;

    /**
     * Find an author by ID
     */
    public function findById(AuthorId $authorId): Author|null;

    /**
     * Find an author by email
     */
    public function findByEmail(AuthorEmail $authorEmail): Author|null;

    /**
     * Check if an author exists by ID
     */
    public function existsById(AuthorId $authorId): bool;

    /**
     * Check if an author exists by email
     */
    public function existsByEmail(AuthorEmail $authorEmail): bool;
}
