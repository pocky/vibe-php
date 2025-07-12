<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Slugger;

interface SluggerInterface
{
    public function slugify(string $string, string $separator = '-'): string;
}
