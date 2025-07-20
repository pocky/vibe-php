<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Service;

use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

interface SlugGeneratorInterface
{
    public function generateFromTitle(Title $title): Slug;
}
