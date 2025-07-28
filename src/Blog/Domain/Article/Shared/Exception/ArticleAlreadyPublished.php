<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Exception;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;

final class ArticleAlreadyPublished extends \DomainException
{
    public function __construct(ArticleId $articleId)
    {
        parent::__construct(
            sprintf('Article "%s" is already published', $articleId->getValue())
        );
    }
}
