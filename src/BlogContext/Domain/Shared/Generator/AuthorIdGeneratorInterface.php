<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Generator;

use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

interface AuthorIdGeneratorInterface
{
    public function nextIdentity(): AuthorId;
}
