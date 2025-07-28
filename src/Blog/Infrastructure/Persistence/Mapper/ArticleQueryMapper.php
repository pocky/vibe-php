<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\ArticleReadModel;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Article as DoctrineArticle;
use App\Blog\Infrastructure\Shared\Mapper\EntityToDomainMapper;

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
            excerpt: $this->generateExcerpt($entity->content),
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
            publishedAt: $entity->publishedAt,
        );
    }

    private function generateExcerpt(string $content, int $maxLength = 200): string
    {
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $content = trim((string) preg_replace('/\s+/', ' ', $content));

        if (strlen($content) <= $maxLength) {
            return $content;
        }

        return substr($content, 0, $maxLength) . '...';
    }
}
