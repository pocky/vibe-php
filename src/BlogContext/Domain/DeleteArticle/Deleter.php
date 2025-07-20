<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteArticle;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final readonly class Deleter implements DeleterInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ArticleId $articleId, string $deletedBy): Model\Article
    {
        $readModel = $this->repository->findById($articleId);

        if (!$readModel instanceof \App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel) {
            throw new Exception\ArticleNotFound($articleId);
        }

        // Create delete data
        $deleteData = Model\Article::create(
            id: $articleId,
            slug: $readModel->slug,
            deletedBy: $deletedBy,
        );

        // Create the domain event
        $event = new Event\ArticleDeleted(
            articleId: $articleId->getValue(),
            slug: $readModel->slug->getValue(),
            deletedBy: $deletedBy,
            deletedAt: $deleteData->deletedAt,
        );

        // Add event to delete data
        $deleteData = $deleteData->withEvents([$event]);

        // Delete from repository
        $this->repository->remove($articleId);

        // Return delete data with events for Application layer to handle
        return $deleteData;
    }
}
