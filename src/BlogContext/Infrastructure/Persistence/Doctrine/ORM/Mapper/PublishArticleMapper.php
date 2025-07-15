<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Domain\PublishArticle\DataPersister\Article;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;

/**
 * Maps PublishArticle domain objects to Doctrine entities
 */
final readonly class PublishArticleMapper implements ArticleMapperInterface
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

        if (!$existingEntity instanceof BlogArticle) {
            throw new \RuntimeException(sprintf('Cannot publish article %s: entity not found', $article->id->getValue()));
        }

        $existingEntity->setStatus($article->status->value);
        $existingEntity->setPublishedAt($article->publishedAt);
        $existingEntity->setUpdatedAt(new \DateTimeImmutable());

        return $existingEntity;
    }
}
