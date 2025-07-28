<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Exception;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;

final class ArticleNotFound extends \RuntimeException
{
    public function __construct(ArticleId $articleId)
    {
        parent::__construct(sprintf('Article not found: %s', $articleId->getValue()));
    }
}
