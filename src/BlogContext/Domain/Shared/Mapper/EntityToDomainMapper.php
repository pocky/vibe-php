<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Mapper;

/**
 * Maps infrastructure entities to domain models.
 *
 * @template TEntity
 * @template TDomain
 */
interface EntityToDomainMapper
{
    /**
     * @param TEntity $entity
     *
     * @return TDomain
     */
    public function map(mixed $entity): mixed;
}
