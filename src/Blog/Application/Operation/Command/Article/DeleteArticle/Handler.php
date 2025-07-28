<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Article\DeleteArticle;

use App\Blog\Domain\Article\ArticleDeleter;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;

final readonly class Handler
{
    public function __construct(
        private ArticleDeleter $deleter,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $articleId = new ArticleId($command->articleId);

        // Execute domain operation (physical deletion)
        ($this->deleter)($articleId);

        // Note: No domain events to dispatch for physical deletion
        // The repository handles the removal directly
    }
}
