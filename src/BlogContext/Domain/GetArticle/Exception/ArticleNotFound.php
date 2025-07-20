<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final class ArticleNotFound extends \DomainException
{
    public function __construct(ArticleId $articleId)
    {
        parent::__construct(sprintf('Article with ID %s not found', $articleId->getValue()));
    }
}
