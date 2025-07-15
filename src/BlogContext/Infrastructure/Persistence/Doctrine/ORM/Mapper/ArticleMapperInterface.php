<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;

/**
 * Interface for mapping domain articles to Doctrine entities
 */
interface ArticleMapperInterface
{
    /**
     * Check if this mapper supports the given article type
     */
    public function supports(object $article): bool;

    /**
     * Map the domain article to a Doctrine entity
     *
     * @param object $article The domain article to map
     * @param BlogArticle|null $existingEntity The existing entity if updating
     *
     * @return BlogArticle The mapped entity (new or updated)
     */
    public function mapToEntity(object $article, BlogArticle|null $existingEntity = null): BlogArticle;
}
