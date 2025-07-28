<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Exception;

final class AuthorAlreadyExists extends \RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Author already exists: %s', $identifier));
    }
}
