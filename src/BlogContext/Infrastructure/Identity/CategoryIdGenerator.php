<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class CategoryIdGenerator
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    public function nextIdentity(): CategoryId
    {
        return new CategoryId($this->generator::generate());
    }
}
