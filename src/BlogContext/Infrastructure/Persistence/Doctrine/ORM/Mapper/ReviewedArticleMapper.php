<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Domain\ReviewArticle\DataPersister\ReviewedArticle;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;

/**
 * Maps ReviewedArticle domain objects to Doctrine entities
 */
final readonly class ReviewedArticleMapper implements ArticleMapperInterface
{
    #[\Override]
    public function supports(object $article): bool
    {
        return $article instanceof ReviewedArticle;
    }

    #[\Override]
    public function mapToEntity(object $article, BlogArticle|null $existingEntity = null): BlogArticle
    {
        /** @var ReviewedArticle $article */

        if (!$existingEntity instanceof BlogArticle) {
            throw new \RuntimeException(sprintf('Cannot review article %s: entity not found', $article->getArticleId()->getValue()));
        }

        $existingEntity->setStatus($article->getStatus()->value);
        $existingEntity->setUpdatedAt(new \DateTimeImmutable());

        return $existingEntity;
    }
}
