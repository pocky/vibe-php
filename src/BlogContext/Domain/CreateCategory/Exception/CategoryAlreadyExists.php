<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory\Exception;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final class CategoryAlreadyExists extends \DomainException
{
    public function __construct(CategoryId $categoryId)
    {
        parent::__construct(
            sprintf(
                'Category with ID "%s" already exists.',
                $categoryId->getValue()
            )
        );
    }
}
