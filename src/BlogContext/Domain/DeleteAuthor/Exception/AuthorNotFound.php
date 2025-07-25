<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteAuthor\Exception;

use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

final class AuthorNotFound extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(AuthorId $authorId): self
    {
        return new self(
            sprintf(
                'Author with ID "%s" was not found.',
                $authorId->getValue()
            )
        );
    }
}
