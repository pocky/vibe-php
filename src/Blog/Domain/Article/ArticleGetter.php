<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article;

use App\Blog\Application\Shared\ReadModel\ArticleReadModel;
use App\Blog\Domain\Article\Shared\Exception\ArticleNotFound;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Repository\ArticleReadRepositoryInterface;

final readonly class ArticleGetter
{
    public function __construct(
        private ArticleReadRepositoryInterface $articleReadRepository,
    ) {
    }

    public function __invoke(ArticleId $articleId): ArticleReadModel
    {
        $readModel = $this->articleReadRepository->findById($articleId);

        if (!$readModel instanceof ArticleReadModel) {
            throw new ArticleNotFound($articleId);
        }

        return $readModel;
    }
}
