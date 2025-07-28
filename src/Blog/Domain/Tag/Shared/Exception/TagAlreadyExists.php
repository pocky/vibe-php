<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Exception;

final class TagAlreadyExists extends \RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Tag already exists: %s', $identifier));
    }
}
