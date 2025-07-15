<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;

/**
 * Maps Doctrine entities to domain Article model
 */
final readonly class EntityToArticleMapper
{
    public function mapToDomain(BlogArticle $entity): Article
    {
        return new Article(
            id: new ArticleId($entity->getId()->toRfc4122()),
            title: new Title($entity->getTitle()),
            content: new Content($entity->getContent()),
            slug: new Slug($entity->getSlug()),
            status: ArticleStatus::fromString($entity->getStatus()),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt() ?? $entity->getCreatedAt(),
            publishedAt: $entity->getPublishedAt(),
        );
    }
}
