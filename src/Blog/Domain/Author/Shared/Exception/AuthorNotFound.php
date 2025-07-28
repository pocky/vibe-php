<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Exception;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;

final class AuthorNotFound extends \RuntimeException
{
    public function __construct(AuthorId $authorId)
    {
        parent::__construct(sprintf('Author not found: %s', $authorId->getValue()));
    }
}
