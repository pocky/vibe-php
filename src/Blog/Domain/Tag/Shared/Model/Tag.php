<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Model;

use App\Blog\Domain\Tag\Shared\Event\TagCreated;
use App\Blog\Domain\Tag\Shared\Event\TagDeleted;
use App\Blog\Domain\Tag\Shared\Event\TagUpdated;
use App\Blog\Domain\Tag\Shared\Exception\TagHasArticles;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;

/**
 * Tag aggregate root with rich business logic.
 */
final class Tag
{
    private array $events = [];

    public function __construct(
        private readonly TagId $tagId,
        private TagName $tagName,
        private TagSlug $tagSlug,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        TagId $tagId,
        TagName $tagName,
        TagSlug $tagSlug,
    ): self {
        $now = new \DateTimeImmutable();

        $tag = new self(
            tagId: $tagId,
            tagName: $tagName,
            tagSlug: $tagSlug,
            createdAt: $now,
            updatedAt: $now,
        );

        $tag->recordEvent(new TagCreated(
            tagId: $tagId->getValue(),
            name: $tagName->getValue(),
            slug: $tagSlug->getValue(),
            createdAt: $now,
        ));

        return $tag;
    }

    public function update(
        TagName|null $name = null,
        TagSlug|null $slug = null,
    ): void {
        $hasChanges = false;

        if ($name instanceof TagName && !$this->tagName->equals($name)) {
            $this->tagName = $name;
            $hasChanges = true;
        }

        if ($slug instanceof TagSlug && !$this->tagSlug->equals($slug)) {
            $this->tagSlug = $slug;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->updatedAt = new \DateTimeImmutable();

            $this->recordEvent(new TagUpdated(
                tagId: $this->tagId->getValue(),
                name: $this->tagName->getValue(),
                slug: $this->tagSlug->getValue(),
                updatedAt: $this->updatedAt,
            ));
        }
    }

    public function delete(int $articleCount = 0): void
    {
        if (0 < $articleCount) {
            throw new TagHasArticles($this->tagId, $articleCount);
        }

        $this->recordEvent(new TagDeleted(
            tagId: $this->tagId->getValue(),
            deletedAt: new \DateTimeImmutable(),
        ));
    }

    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    // Getters
    public function getId(): TagId
    {
        return $this->tagId;
    }

    public function id(): TagId
    {
        return $this->tagId;
    }

    public function getName(): TagName
    {
        return $this->tagName;
    }

    public function name(): TagName
    {
        return $this->tagName;
    }

    public function getSlug(): TagSlug
    {
        return $this->tagSlug;
    }

    public function slug(): TagSlug
    {
        return $this->tagSlug;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
