<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\Generator;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;

interface CategoryIdGeneratorInterface
{
    public function nextIdentity(): CategoryId;
}
