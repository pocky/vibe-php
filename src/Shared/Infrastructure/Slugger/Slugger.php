<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Slugger;

use Cocur\Slugify\Slugify;

final readonly class Slugger implements SluggerInterface
{
    #[\Override]
    public function slugify(string $string, string $separator = '-'): string
    {
        $slug = new Slugify()->slugify($string, $separator);

        // Trim to maximum length if needed (same as Slug ValueObject)
        if (250 < strlen($slug)) {
            $slug = substr($slug, 0, 250);
            // Remove potential trailing separator
            $slug = rtrim($slug, $separator);
        }

        return $slug;
    }
}
