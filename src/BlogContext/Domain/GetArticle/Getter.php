<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticle;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final readonly class Getter implements GetterInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ArticleId $articleId): Model\Article
    {
        $readModel = $this->repository->findById($articleId);

        if (!$readModel instanceof \App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel) {
            throw new Exception\ArticleNotFound($articleId);
        }

        // Create get data from read model
        return Model\Article::fromReadModel($readModel);
    }
}
