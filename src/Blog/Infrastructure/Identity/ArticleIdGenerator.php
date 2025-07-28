<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Identity;

use App\Blog\Application\Shared\Generator\ArticleIdGeneratorInterface;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
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
