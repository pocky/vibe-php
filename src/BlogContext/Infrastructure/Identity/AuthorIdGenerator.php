<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\Generator\AuthorIdGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class AuthorIdGenerator implements AuthorIdGeneratorInterface
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    #[\Override]
    public function nextIdentity(): AuthorId
    {
        return new AuthorId($this->generator::generate());
    }
}
