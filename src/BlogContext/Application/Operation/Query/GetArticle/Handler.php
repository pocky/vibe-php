<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetArticle;

use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class Handler
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Query $query): Article
    {
        $articleId = new ArticleId($query->id);
        $article = $this->repository->findById($articleId);

        if (!$article instanceof Article) {
            throw new \RuntimeException('Article not found');
        }

        return $article;
    }
}
