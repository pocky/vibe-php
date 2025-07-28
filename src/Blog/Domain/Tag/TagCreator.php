<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag;

use App\Blog\Domain\Tag\Shared\Exception\TagAlreadyExists;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;

final readonly class TagCreator
{
    public function __construct(
        private TagWriteRepositoryInterface $tagRepository,
    ) {
    }

    public function __invoke(
        TagId $tagId,
        TagName $tagName,
        TagSlug|null $slug = null,
    ): Tag {
        // Generate slug from name if not provided
        if (!$slug instanceof TagSlug) {
            $slug = TagSlug::generateFromName($tagName->getValue());
        }

        // Check if slug already exists
        $existingTag = $this->tagRepository->findBySlug($slug);
        if ($existingTag instanceof Tag) {
            throw new TagAlreadyExists($slug->getValue());
        }

        // Create domain model using aggregate factory method
        $tag = Tag::create(
            tagId: $tagId,
            tagName: $tagName,
            tagSlug: $slug,
        );

        // Persist
        $this->tagRepository->add($tag);

        // Return aggregate with unreleased events for Application layer to handle
        return $tag;
    }
}
