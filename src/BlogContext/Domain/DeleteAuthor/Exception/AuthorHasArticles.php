<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteAuthor\Exception;

use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

final class AuthorHasArticles extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withArticleCount(AuthorId $authorId, int $articleCount): self
    {
        return new self(
            sprintf(
                'Cannot delete author with ID "%s" because they have %d articles.',
                $authorId->getValue(),
                $articleCount
            )
        );
    }
}
