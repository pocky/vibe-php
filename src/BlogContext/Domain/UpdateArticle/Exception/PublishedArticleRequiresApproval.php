<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final class PublishedArticleRequiresApproval extends UpdateArticleException
{
    public function __construct(
        private readonly ArticleId $articleId,
    ) {
        parent::__construct(
            sprintf('Published article with ID "%s" requires editor approval for major changes', $articleId->getValue())
        );
    }

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }
}
