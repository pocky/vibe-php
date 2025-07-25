<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Service;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\Service\SlugGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\Name;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use Cocur\Slugify\SlugifyInterface;

final readonly class SlugGenerator implements SlugGeneratorInterface
{
    public function __construct(
        private SlugifyInterface $slugify,
        private ArticleRepositoryInterface $articleRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    #[\Override]
    public function generateFromTitle(Title $title): Slug
    {
        $baseSlug = $this->slugify->slugify($title->getValue());

        // Ensure it doesn't exceed max length
        if (240 < strlen($baseSlug)) {
            $baseSlug = substr($baseSlug, 0, 240);
        }

        // Check for uniqueness
        $slug = new Slug($baseSlug);
        $suffix = 1;

        while ($this->articleRepository->existsWithSlug($slug)) {
            $slugWithSuffix = $baseSlug . '-' . $suffix;
            if (250 < strlen($slugWithSuffix)) {
                // Trim base slug to make room for suffix
                $maxBaseLength = 250 - strlen('-' . $suffix);
                $baseSlug = substr($baseSlug, 0, $maxBaseLength);
                $slugWithSuffix = $baseSlug . '-' . $suffix;
            }
            $slug = new Slug($slugWithSuffix);
            ++$suffix;
        }

        return $slug;
    }

    #[\Override]
    public function generateFromName(Name $name): Slug
    {
        $baseSlug = $this->slugify->slugify($name->getValue());

        // Ensure it doesn't exceed max length
        if (240 < strlen($baseSlug)) {
            $baseSlug = substr($baseSlug, 0, 240);
        }

        // Check for uniqueness
        $slug = new Slug($baseSlug);
        $suffix = 1;

        while ($this->categoryRepository->existsWithSlug($slug)) {
            $slugWithSuffix = $baseSlug . '-' . $suffix;
            if (250 < strlen($slugWithSuffix)) {
                // Trim base slug to make room for suffix
                $maxBaseLength = 250 - strlen('-' . $suffix);
                $baseSlug = substr($baseSlug, 0, $maxBaseLength);
                $slugWithSuffix = $baseSlug . '-' . $suffix;
            }
            $slug = new Slug($slugWithSuffix);
            ++$suffix;
        }

        return $slug;
    }
}
