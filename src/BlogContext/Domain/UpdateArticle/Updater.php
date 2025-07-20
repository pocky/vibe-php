<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final readonly class Updater implements UpdaterInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        ArticleId $articleId,
        Title|null $title = null,
        Content|null $content = null,
        Slug|null $slug = null,
    ): Model\Article {
        $readModel = $this->repository->findById($articleId);

        if (!$readModel instanceof \App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel) {
            throw new Exception\ArticleNotFound($articleId);
        }

        // If slug is provided, ensure it's unique (excluding current article)
        if ($slug instanceof Slug && !$readModel->slug->equals($slug)) {
            $existingArticle = $this->repository->findBySlug($slug);
            if ($existingArticle && !$existingArticle->id->equals($articleId)) {
                throw new Exception\SlugAlreadyExists($slug);
            }
        }

        // Use current values if not provided
        $finalTitle = $title ?? $readModel->title;
        $finalContent = $content ?? $readModel->content;
        $finalSlug = $slug ?? $readModel->slug;

        // Create UpdateArticle directly from ReadModel
        $updateData = Model\Article::fromReadModel($readModel);

        // Update the article data
        $updatedData = $updateData->update($finalTitle, $finalContent, $finalSlug);

        // Create event if there were changes
        if ($updatedData->hasChanges()) {
            $event = new Event\ArticleUpdated(
                articleId: $articleId->getValue(),
                title: $updatedData->title->getValue(),
                slug: $updatedData->slug->getValue(),
                updatedAt: $updatedData->timestamps->getUpdatedAt(),
            );

            $updatedData = $updatedData->withEvents([$event]);
        }

        // Persist changes
        $this->repository->update($updatedData);

        return $updatedData;
    }
}
