<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Exception;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;

final class AuthorHasArticles extends \DomainException
{
    public function __construct(AuthorId $authorId, int $articleCount)
    {
        parent::__construct(
            sprintf(
                'Cannot delete author "%s" because they have %d article(s)',
                $authorId->getValue(),
                $articleCount
            )
        );
    }
}
