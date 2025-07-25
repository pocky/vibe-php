<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateAuthor\Exception;

use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

final class AuthorAlreadyExists extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(AuthorId $authorId): self
    {
        return new self(
            sprintf(
                'Author with ID "%s" already exists.',
                $authorId->getValue()
            )
        );
    }

    public static function withEmail(AuthorEmail $email): self
    {
        return new self(
            sprintf(
                'Author with email "%s" already exists.',
                $email->getValue()
            )
        );
    }
}
