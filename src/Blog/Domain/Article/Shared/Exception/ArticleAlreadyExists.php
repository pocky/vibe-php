<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Exception;

final class ArticleAlreadyExists extends \RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Article already exists: %s', $identifier));
    }
}
