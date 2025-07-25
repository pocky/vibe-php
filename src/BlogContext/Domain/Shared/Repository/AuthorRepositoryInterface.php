<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthorModel;
use App\BlogContext\Domain\DeleteAuthor\Model\Author as DeleteAuthorModel;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\UpdateAuthor\Model\Author as UpdateAuthorModel;

interface AuthorRepositoryInterface
{
    // Create operations
    public function add(CreateAuthorModel $author): void;

    // Read operations
    public function findById(AuthorId $id): CreateAuthorModel|null;

    public function findByEmail(AuthorEmail $email): CreateAuthorModel|null;

    public function existsById(AuthorId $id): bool;

    public function existsByEmail(AuthorEmail $email): bool;

    /**
     * @return CreateAuthorModel[]
     */
    public function findAllPaginated(int $limit, int $offset): array;

    public function countAll(): int;

    // Update operations
    public function update(UpdateAuthorModel $author): void;

    // Delete operations
    public function remove(DeleteAuthorModel $author): void;

    public function countArticlesByAuthorId(AuthorId $id): int;
}
