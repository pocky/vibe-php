<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteArticle\Builder;

use App\BlogContext\Domain\DeleteArticle\Model\Article;
use App\BlogContext\Domain\Shared\Builder\ArticleBuilderInterface;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Slug;

/**
 * Builder for creating Article models in the DeleteArticle context.
 */
final class ArticleBuilder implements ArticleBuilderInterface
{
    public function fromReadModel(ArticleReadModel $readModel): Article
    {
        throw new \LogicException('DeleteArticle builder should use createDeleted method');
    }

    public function fromArray(array $data): Article
    {
        throw new \LogicException('DeleteArticle builder should use createDeleted method');
    }

    /**
     * Create a deleted article record.
     */
    public function createDeleted(
        ArticleId $id,
        Slug $slug,
        string $deletedBy,
        array $events = []
    ): Article {
        return Article::create(
            id: $id,
            slug: $slug,
            deletedBy: $deletedBy
        )->withEvents($events);
    }
}
