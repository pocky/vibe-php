<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Builder;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;

/**
 * Interface for building article models from various sources.
 * Replaces the DataMapper pattern with a more flexible Builder pattern.
 */
interface ArticleBuilderInterface
{
    /**
     * Build from an ArticleReadModel.
     */
    public function fromReadModel(ArticleReadModel $readModel): object;

    /**
     * Build from raw data array.
     */
    public function fromArray(array $data): object;
}
