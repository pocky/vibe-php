<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Exception;

final class CategoryAlreadyExists extends \RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Category already exists: %s', $identifier));
    }
}
