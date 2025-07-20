<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Builder;

use App\BlogContext\Domain\PublishArticle\Model\Article;
use App\BlogContext\Domain\Shared\Builder\ArticleBuilderInterface;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;

/**
 * Builder for creating Article models in the PublishArticle context.
 */
final class ArticleBuilder implements ArticleBuilderInterface
{
    public function fromReadModel(ArticleReadModel $readModel): Article
    {
        return new Article(
            id: $readModel->id,
            slug: $readModel->slug,
            status: $readModel->status,
            timestamps: $readModel->timestamps,
            publishedAt: $readModel->publishedAt
        );
    }

    public function fromArray(array $data): Article
    {
        throw new \LogicException('PublishArticle builder should use fromReadModel method');
    }

    /**
     * Create a published article.
     */
    public function publish(
        ArticleReadModel $readModel,
        \DateTimeImmutable $publishedAt,
        array $events = []
    ): Article {
        return new Article(
            id: $readModel->id,
            slug: $readModel->slug,
            status: ArticleStatus::PUBLISHED,
            timestamps: $readModel->timestamps->withUpdatedAt($publishedAt),
            publishedAt: $publishedAt,
            events: $events
        );
    }
}
