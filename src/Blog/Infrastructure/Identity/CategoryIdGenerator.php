<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Identity;

use App\Blog\Application\Shared\Generator\CategoryIdGeneratorInterface;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
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
