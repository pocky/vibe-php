<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\Generator\ArticleIdGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class ArticleIdGenerator implements ArticleIdGeneratorInterface
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    #[\Override]
    public function nextIdentity(): ArticleId
    {
        return new ArticleId($this->generator::generate());
    }
}
