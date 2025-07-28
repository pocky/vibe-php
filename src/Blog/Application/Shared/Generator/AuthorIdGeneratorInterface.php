<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\Generator;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;

interface AuthorIdGeneratorInterface
{
    public function nextIdentity(): AuthorId;
}
