<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final class ArticleAlreadyExists extends \DomainException
{
    public function __construct(ArticleId $articleId)
    {
        parent::__construct(
            sprintf(
                'Article with ID "%s" already exists.',
                $articleId->getValue()
            )
        );
    }
}
