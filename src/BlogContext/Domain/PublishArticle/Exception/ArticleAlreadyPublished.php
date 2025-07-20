<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final class ArticleAlreadyPublished extends \DomainException
{
    public function __construct(ArticleId $articleId)
    {
        parent::__construct(sprintf('Article with ID %s is already published', $articleId->getValue()));
    }
}
