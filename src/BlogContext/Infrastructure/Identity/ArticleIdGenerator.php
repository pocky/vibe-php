<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class ArticleIdGenerator
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    public function nextIdentity(): ArticleId
    {
        return new ArticleId($this->generator::generate());
    }
}
