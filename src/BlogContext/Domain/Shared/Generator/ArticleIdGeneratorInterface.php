<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Generator;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

interface ArticleIdGeneratorInterface
{
    public function nextIdentity(): ArticleId;
}
