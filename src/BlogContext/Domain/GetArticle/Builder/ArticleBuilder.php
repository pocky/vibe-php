<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticle\Builder;

use App\BlogContext\Domain\GetArticle\Model\Article;
use App\BlogContext\Domain\Shared\Builder\ArticleBuilderInterface;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;

/**
 * Builder for creating Article models in the GetArticle context.
 */
final class ArticleBuilder implements ArticleBuilderInterface
{
    public function fromReadModel(ArticleReadModel $readModel): Article
    {
        return Article::fromReadModel($readModel);
    }

    public function fromArray(array $data): Article
    {
        throw new \LogicException('GetArticle builder should use fromReadModel method');
    }
}
