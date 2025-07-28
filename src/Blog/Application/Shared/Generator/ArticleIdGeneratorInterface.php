<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\Generator;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;

interface ArticleIdGeneratorInterface
{
    public function nextIdentity(): ArticleId;
}
