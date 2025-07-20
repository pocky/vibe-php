<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticles\Builder;

use App\BlogContext\Domain\GetArticles\Model\Article;
use App\BlogContext\Domain\Shared\Builder\ArticleBuilderInterface;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;

/**
 * Builder for creating Article models in the GetArticles context.
 */
final class ArticleBuilder implements ArticleBuilderInterface
{
    public function fromReadModel(ArticleReadModel $readModel): Article
    {
        return Article::fromReadModel($readModel);
    }

    public function fromArray(array $data): Article
    {
        throw new \LogicException('GetArticles builder should use fromReadModel method');
    }

    /**
     * Build a collection of articles from read models.
     *
     * @param ArticleReadModel[] $readModels
     *
     * @return Article[]
     */
    public function fromReadModels(array $readModels): array
    {
        return array_map(
            fn (ArticleReadModel $readModel) => $this->fromReadModel($readModel),
            $readModels
        );
    }
}
