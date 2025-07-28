<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag;

use App\Blog\Domain\Tag\Shared\Exception\TagNotFound;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;

final readonly class TagDeleter
{
    public function __construct(
        private TagWriteRepositoryInterface $tagRepository,
    ) {
    }

    public function __invoke(TagId $tagId): Tag
    {
        // Find the tag
        $tag = $this->tagRepository->findById($tagId);
        if (!$tag instanceof Tag) {
            throw TagNotFound::withId($tagId);
        }

        // Check business rules and delete
        $articleCount = $this->tagRepository->countArticles($tagId);
        $tag->delete($articleCount);

        // Remove from repository
        $this->tagRepository->remove($tag);

        // Return tag with unreleased events for Application layer to handle
        return $tag;
    }
}
