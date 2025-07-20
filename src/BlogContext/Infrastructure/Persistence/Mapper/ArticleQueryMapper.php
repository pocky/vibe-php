<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Mapper;

use App\BlogContext\Domain\Shared\Mapper\EntityToDomainMapper;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;

/**
 * Maps Doctrine entity to ArticleReadModel for query operations.
 *
 * @implements EntityToDomainMapper<DoctrineArticle, ArticleReadModel>
 */
final class ArticleQueryMapper implements EntityToDomainMapper
{
    public function map(mixed $entity): ArticleReadModel
    {
        assert($entity instanceof DoctrineArticle);

        return new ArticleReadModel(
            id: new ArticleId($entity->id->toRfc4122()),
            title: new Title($entity->title),
            content: new Content($entity->content),
            slug: new Slug($entity->slug),
            status: ArticleStatus::from($entity->status),
            authorId: $entity->authorId,
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
            publishedAt: $entity->publishedAt,
        );
    }
}
