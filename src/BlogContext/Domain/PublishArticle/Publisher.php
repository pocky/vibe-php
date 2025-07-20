<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\UpdateArticle\Model\Article as UpdateArticle;

final readonly class Publisher implements PublisherInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        ArticleId $articleId,
        \DateTimeImmutable|null $publishAt = null,
    ): Model\Article {
        $readModel = $this->repository->findById($articleId);

        if (!$readModel instanceof \App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel) {
            throw new Exception\ArticleNotFound($articleId);
        }

        // Create PublishArticle directly from ReadModel
        $publishData = Model\Article::fromReadModel($readModel);

        // Publish the article
        $publishedData = $publishData->publish($publishAt);

        // Create the event
        $event = new Event\ArticlePublished(
            articleId: $articleId->getValue(),
            slug: $publishedData->slug->getValue(),
            publishedAt: $publishedData->publishedAt ?? new \DateTimeImmutable(),
        );

        $publishedData = $publishedData->withEvents([$event]);

        // Create UpdateArticle for persistence
        $updateData = new UpdateArticle(
            id: $readModel->id,
            title: $readModel->title,
            content: $readModel->content,
            slug: $readModel->slug,
            status: $publishedData->status,
            authorId: $readModel->authorId,
            timestamps: $publishedData->timestamps,
            publishedAt: $publishedData->publishedAt,
        );

        // Persist changes
        $this->repository->update($updateData);

        return $publishedData;
    }
}
