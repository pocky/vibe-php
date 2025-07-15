<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;

/**
 * Registry for managing article mappers
 */
final class ArticleMappingRegistry
{
    /**
     * @var array<class-string, ArticleMapperInterface>
     */
    private array $mappers = [];

    /**
     * Register a mapper for a specific article class
     *
     * @param class-string $articleClass The fully qualified class name of the article
     */
    public function register(string $articleClass, ArticleMapperInterface $mapper): void
    {
        $this->mappers[$articleClass] = $mapper;
    }

    /**
     * Get the appropriate mapper for the given article
     *
     * @throws \InvalidArgumentException When no mapper is found for the article type
     */
    public function getMapper(object $article): ArticleMapperInterface
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($article)) {
                return $mapper;
            }
        }

        throw new \InvalidArgumentException(sprintf('No mapper found for article type: %s', $article::class));
    }

    /**
     * Map an article to entity using the appropriate mapper
     */
    public function mapToEntity(object $article, BlogArticle|null $existingEntity = null): BlogArticle
    {
        return $this->getMapper($article)->mapToEntity($article, $existingEntity);
    }

    /**
     * Check if the registry has a mapper for the given article
     */
    public function hasMapper(object $article): bool
    {
        try {
            $this->getMapper($article);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
