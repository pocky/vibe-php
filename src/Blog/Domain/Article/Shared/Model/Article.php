<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Model;

use App\Blog\Domain\Article\Shared\Event\ArticleCreated;
use App\Blog\Domain\Article\Shared\Event\ArticlePublished;
use App\Blog\Domain\Article\Shared\Event\ArticleUpdated;
use App\Blog\Domain\Article\Shared\Exception\ArticleAlreadyPublished;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;

/**
 * Article Aggregate Root - Rich domain model with business behavior
 */
final class Article
{
    private array $events = [];

    /** @var array<TagId> */
    private array $tagIds = [];

    private CategoryId|null $categoryId = null;

    private readonly \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly ArticleId $articleId,
        private Title $title,
        private Content $content,
        private Slug $slug,
        private ArticleStatus $articleStatus,
        private readonly AuthorId $authorId,
        \DateTimeImmutable|null $createdAt = null,
        \DateTimeImmutable|null $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public static function create(
        ArticleId $articleId,
        Title $title,
        Content $content,
        Slug $slug,
        AuthorId $authorId,
    ): self {
        $article = new self(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            articleStatus: ArticleStatus::DRAFT,
            authorId: $authorId
        );

        $article->recordEvent(new ArticleCreated(
            articleId: $articleId->getValue(),
            title: $title->getValue(),
            content: $content->getValue(),
            slug: $slug->getValue(),
            authorId: $authorId->getValue(),
            createdAt: $article->createdAt
        ));

        return $article;
    }

    public function update(Title $title, Content $content, Slug $slug): void
    {
        $hasChanges = false;

        if (!$this->title->equals($title)) {
            $this->title = $title;
            $hasChanges = true;
        }

        if (!$this->content->equals($content)) {
            $this->content = $content;
            $hasChanges = true;
        }

        if (!$this->slug->equals($slug)) {
            $this->slug = $slug;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->updatedAt = new \DateTimeImmutable();
            $this->recordEvent(new ArticleUpdated(
                articleId: $this->articleId->getValue(),
                title: $title->getValue(),
                content: $content->getValue(),
                slug: $slug->getValue(),
                updatedAt: $this->updatedAt
            ));
        }
    }

    public function publish(): void
    {
        if (ArticleStatus::PUBLISHED === $this->articleStatus) {
            throw new ArticleAlreadyPublished($this->articleId);
        }

        // Since we only have DRAFT and PUBLISHED, if it's not PUBLISHED, it must be DRAFT
        $this->articleStatus = ArticleStatus::PUBLISHED;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new ArticlePublished(
            articleId: $this->articleId->getValue(),
            publishedAt: $this->updatedAt
        ));
    }

    public function assignToCategory(CategoryId $categoryId): void
    {
        if (true === $this->categoryId?->equals($categoryId)) {
            return; // Already assigned to this category
        }

        $this->categoryId = $categoryId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function removeFromCategory(): void
    {
        if (!$this->categoryId instanceof CategoryId) {
            return; // Already has no category
        }

        $this->categoryId = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function addTag(TagId $tagId): void
    {
        $tagValue = $tagId->getValue();
        if (in_array($tagValue, array_map(fn (TagId $tagId): string => $tagId->getValue(), $this->tagIds), true)) {
            return; // Already has this tag
        }

        $this->tagIds[] = $tagId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function removeTag(TagId $tagId): void
    {
        $this->tagIds = array_filter(
            $this->tagIds,
            fn (TagId $id): bool => !$id->equals($tagId)
        );
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function clearTags(): void
    {
        if ([] === $this->tagIds) {
            return;
        }

        $this->tagIds = [];
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters
    public function id(): ArticleId
    {
        return $this->articleId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function content(): Content
    {
        return $this->content;
    }

    public function slug(): Slug
    {
        return $this->slug;
    }

    public function status(): ArticleStatus
    {
        return $this->articleStatus;
    }

    public function authorId(): AuthorId
    {
        return $this->authorId;
    }

    public function categoryId(): CategoryId|null
    {
        return $this->categoryId;
    }

    /**
     * @return array<TagId>
     */
    public function tagIds(): array
    {
        return $this->tagIds;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isPublished(): bool
    {
        return ArticleStatus::PUBLISHED === $this->articleStatus;
    }

    public function isDraft(): bool
    {
        return ArticleStatus::DRAFT === $this->articleStatus;
    }

    // Event handling
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
}
