<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;
use Symfony\Component\Uid\Uuid;

/**
 * Maps CreateArticle domain objects to Doctrine entities
 */
final readonly class CreateArticleMapper implements ArticleMapperInterface
{
    #[\Override]
    public function supports(object $article): bool
    {
        return $article instanceof Article;
    }

    #[\Override]
    public function mapToEntity(object $article, BlogArticle|null $existingEntity = null): BlogArticle
    {
        /** @var Article $article */

        if ($existingEntity instanceof BlogArticle) {
            // Update existing entity
            $existingEntity->setTitle($article->title->getValue());
            $existingEntity->setContent($article->content->getValue());
            $existingEntity->setSlug($article->slug->getValue());
            $existingEntity->setStatus($article->status->value);
            $existingEntity->setUpdatedAt(new \DateTimeImmutable());

            return $existingEntity;
        }

        // Create new entity
        return new BlogArticle(
            id: Uuid::fromString($article->id->getValue()),
            title: $article->title->getValue(),
            content: $article->content->getValue(),
            slug: $article->slug->getValue(),
            status: $article->status->value,
            createdAt: $article->createdAt,
            updatedAt: $article->createdAt,
        );
    }
}
