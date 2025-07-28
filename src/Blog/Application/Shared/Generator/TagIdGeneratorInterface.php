<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\Generator;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;

interface TagIdGeneratorInterface
{
    public function nextIdentity(): TagId;
}
