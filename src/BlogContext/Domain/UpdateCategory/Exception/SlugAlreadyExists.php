<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateCategory\Exception;

use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

final class SlugAlreadyExists extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forSlug(CategorySlug $slug): self
    {
        return new self(sprintf('A category with slug "%s" already exists.', $slug->getValue()));
    }
}
