<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

final readonly class Creator implements CreatorInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        ArticleId $articleId,
        Title $title,
        Content $content,
        Slug $slug,
        string $authorId,
    ): Model\Article {
        // Verify slug uniqueness
        if ($this->repository->existsWithSlug($slug)) {
            throw new ArticleAlreadyExists($articleId);
        }

        // Create the article data
        $articleData = Model\Article::create(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            authorId: $authorId,
        );

        // Create the domain event
        $event = new Event\ArticleCreated(
            articleId: $articleId->getValue(),
            title: $title->getValue(),
            authorId: $authorId,
            status: ArticleStatus::DRAFT->value,
            createdAt: $articleData->timestamps->getCreatedAt(),
        );

        // Add event to article data
        $articleData = $articleData->withEvents([$event]);

        // Persist
        $this->repository->add($articleData);

        // Return article data with events for Application layer to handle
        return $articleData;
    }
}
