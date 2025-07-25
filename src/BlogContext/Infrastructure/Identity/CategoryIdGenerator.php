<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\Generator\CategoryIdGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class CategoryIdGenerator implements CategoryIdGeneratorInterface
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    #[\Override]
    public function nextIdentity(): CategoryId
    {
        return new CategoryId($this->generator::generate());
    }
}
