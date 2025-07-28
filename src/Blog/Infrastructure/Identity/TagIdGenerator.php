<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Identity;

use App\Blog\Application\Shared\Generator\TagIdGeneratorInterface;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class TagIdGenerator implements TagIdGeneratorInterface
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    #[\Override]
    public function nextIdentity(): TagId
    {
        return new TagId($this->generator::generate());
    }
}
