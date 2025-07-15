<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Domain\UpdateArticle\DataPersister\Article;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;

/**
 * Maps UpdateArticle domain objects to Doctrine entities
 */
final readonly class UpdateArticleMapper implements ArticleMapperInterface
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
            $existingEntity->setTitle($article->title->getValue());
            $existingEntity->setContent($article->content->getValue());
            $existingEntity->setSlug($article->slug->getValue());
            $existingEntity->setStatus($article->status->value);
            $existingEntity->setUpdatedAt($article->updatedAt);

            return $existingEntity;
        }

        // This shouldn't happen for updates, but handle it gracefully
        throw new \RuntimeException(sprintf('Cannot update article %s: entity not found', $article->id->getValue()));
    }
}
