<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Identity;

use App\Blog\Application\Shared\Generator\AuthorIdGeneratorInterface;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
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
