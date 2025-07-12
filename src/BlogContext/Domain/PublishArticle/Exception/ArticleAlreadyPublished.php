<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final class ArticleAlreadyPublished extends PublishArticleException
{
    public function __construct(
        private readonly ArticleId $articleId,
    ) {
        parent::__construct(
            sprintf('Article with ID "%s" is already published', $articleId->getValue())
        );
    }

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }
}
