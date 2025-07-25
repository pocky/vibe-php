<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory\Exception;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

final class CategoryAlreadyExists extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(CategoryId $categoryId): self
    {
        return new self(
            sprintf(
                'Category with ID "%s" already exists.',
                $categoryId->getValue()
            )
        );
    }

    public static function withSlug(CategorySlug $slug): self
    {
        return new self(
            sprintf(
                'Category with slug "%s" already exists.',
                $slug->getValue()
            )
        );
    }

    public static function withName(CategoryName $name): self
    {
        return new self(
            sprintf(
                'Category with name "%s" already exists.',
                $name->getValue()
            )
        );
    }
}
