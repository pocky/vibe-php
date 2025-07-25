<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategory\Exception;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final class CategoryNotFound extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(CategoryId $categoryId): self
    {
        return new self(sprintf('Category with ID "%s" not found.', $categoryId->getValue()));
    }
}
