<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Tag;

use App\Blog\Domain\Tag\Shared\Exception\TagAlreadyExists;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Domain\Tag\TagCreator;
use PHPUnit\Framework\TestCase;

final class TagCreatorTest extends TestCase
{
    private TagCreator $creator;

    private \PHPUnit\Framework\MockObject\MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TagWriteRepositoryInterface::class);
        $this->creator = new TagCreator($this->repository);
    }

    public function test_it_creates_tag_with_all_properties(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');
        new \DateTimeImmutable('2024-01-01 10:00:00');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($tagSlug)
            ->willReturn(null);

        $tag = ($this->creator)(
            tagId: $tagId,
            tagName: $tagName,
            slug: $tagSlug
        );

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame($tagId, $tag->id());
        $this->assertSame($tagName, $tag->name());
        $this->assertSame($tagSlug, $tag->slug());
        $this->assertInstanceOf(\DateTimeImmutable::class, $tag->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $tag->updatedAt());
        $this->assertSame(0, $tag->articleCount());
    }

    public function test_it_creates_tag_with_auto_generated_slug(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Web Development');
        new \DateTimeImmutable('2024-01-01 10:00:00');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($this->callback(fn (TagSlug $tagSlug): bool => 'web-development' === $tagSlug->getValue()))
            ->willReturn(null);

        $tag = ($this->creator)(
            tagId: $tagId,
            tagName: $tagName,
            slug: null
        );

        $this->assertSame('web-development', $tag->slug()->getValue());
    }

    public function test_it_uses_current_time_when_created_at_not_provided(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');
        $beforeCreation = new \DateTimeImmutable();

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($tagSlug)
            ->willReturn(null);

        $tag = ($this->creator)(
            id: $tagId,
            name: $tagName,
            slug: $tagSlug,
            createdAt: null
        );

        $afterCreation = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($beforeCreation, $tag->createdAt());
        $this->assertLessThanOrEqual($afterCreation, $tag->createdAt());
    }

    public function test_it_records_tag_created_event(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($tagSlug)
            ->willReturn(null);

        $tag = ($this->creator)(
            id: $tagId,
            name: $tagName,
            slug: $tagSlug
        );

        $events = $tag->getEvents();
        $this->assertCount(1, $events);
        $this->assertSame('Blog.Tag.Created', $events[0]->eventType());
        $this->assertSame($tagId->getValue(), $events[0]->tagId);
        $this->assertSame($tagName->getValue(), $events[0]->name);
        $this->assertSame($tagSlug->getValue(), $events[0]->slug);
    }

    public function test_it_validates_unique_slug_before_creation(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $existingTag = Tag::create(
            new TagId('existing-tag-id'),
            $tagName,
            $tagSlug
        );

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($tagSlug)
            ->willReturn($existingTag);

        $this->expectException(TagAlreadyExists::class);
        $this->expectExceptionMessage('Tag with slug "technology" already exists');

        ($this->creator)(
            id: $tagId,
            name: $tagName,
            slug: $tagSlug
        );
    }

    public function test_it_creates_tag_when_slug_is_unique(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with($tagSlug)
            ->willReturn(null);

        $tag = ($this->creator)(
            id: $tagId,
            name: $tagName,
            slug: $tagSlug
        );

        $this->assertSame($tagSlug, $tag->slug());
    }
}
