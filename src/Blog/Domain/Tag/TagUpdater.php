<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag;

use App\Blog\Domain\Tag\Shared\Exception\TagNotFound;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;

final readonly class TagUpdater
{
    public function __construct(
        private TagWriteRepositoryInterface $tagRepository,
    ) {
    }

    public function __invoke(
        TagId $tagId,
        TagName|null $name = null,
        TagSlug|null $slug = null,
    ): Tag {
        // Find the tag
        $tag = $this->tagRepository->findById($tagId);
        if (!$tag instanceof Tag) {
            throw new TagNotFound($tagId);
        }

        // Business rule: Check if new slug is unique (if changed)
        if ($slug instanceof TagSlug && !$tag->getSlug()->equals($slug) && $this->tagRepository->existsBySlugExcludingId($slug, $tagId)) {
            throw new \DomainException(sprintf('Slug "%s" already exists', $slug->getValue()));
        }

        // Update the tag using aggregate method
        $tag->update(
            name: $name,
            slug: $slug,
        );

        // Persist
        $this->tagRepository->update($tag);

        // Return aggregate with unreleased events for Application layer to handle
        return $tag;
    }
}
