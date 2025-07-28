<?php

declare(strict_types=1);

namespace App\Blog\Domain\Shared\Service;

use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\ValueObject\Name;
use App\Blog\Domain\Shared\ValueObject\Slug;

interface SlugGeneratorInterface
{
    public function generateFromTitle(Title $title): Slug;

    public function generateFromName(Name $name): Slug;
}
