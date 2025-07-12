<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final class ArticleNotReady extends PublishArticleException
{
    public function __construct(
        private readonly ArticleId $articleId,
        private readonly string $reason,
    ) {
        parent::__construct(
            sprintf('Article with ID "%s" is not ready for publication: %s', $articleId->getValue(), $reason)
        );
    }

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
