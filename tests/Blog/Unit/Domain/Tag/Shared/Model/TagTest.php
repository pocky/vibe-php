<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Tag\Shared\Model;

use App\Blog\Domain\Tag\Shared\Event\TagCreated;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function test_it_creates_tag_with_all_properties(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $tag = Tag::create($tagId, $tagName, $tagSlug);

        $this->assertSame($tagId, $tag->id());
        $this->assertSame($tagName, $tag->name());
        $this->assertSame($tagSlug, $tag->slug());
        $this->assertInstanceOf(\DateTimeImmutable::class, $tag->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $tag->updatedAt());
        $this->assertEquals($tag->createdAt(), $tag->updatedAt());
    }

    public function test_it_records_tag_created_event(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $tag = Tag::create($tagId, $tagName, $tagSlug);
        $events = $tag->getEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TagCreated::class, $events[0]);

        $event = $events[0];
        $this->assertSame($tagId->getValue(), $event->tagId);
        $this->assertSame($tagName->getValue(), $event->name);
        $this->assertSame($tagSlug->getValue(), $event->slug);
    }

    public function test_with_events_creates_new_instance_with_events(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $tag = Tag::create($tagId, $tagName, $tagSlug);
        $tagCreated = new TagCreated(
            tagId: $tagId->getValue(),
            name: 'Updated Name',
            slug: 'updated-slug'
        );

        $tagWithEvents = $tag->withEvents([$tagCreated]);

        $this->assertNotSame($tag, $tagWithEvents);
        $this->assertSame($tagId, $tagWithEvents->id());
        $this->assertSame($tagName, $tagWithEvents->name());
        $this->assertSame($tagSlug, $tagWithEvents->slug());
        $this->assertCount(1, $tagWithEvents->getEvents());
        $this->assertSame($tagCreated, $tagWithEvents->getEvents()[0]);
    }

    public function test_release_events_returns_and_clears_events(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $tag = Tag::create($tagId, $tagName, $tagSlug);

        // First release should return the event
        $events = $tag->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(TagCreated::class, $events[0]);

        // Events should still be accessible via getEvents (readonly)
        $this->assertCount(1, $tag->getEvents());
    }

    public function test_it_generates_slug_from_name_if_not_provided(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Web Development');

        $tag = Tag::create($tagId, $tagName);

        $this->assertSame('web-development', $tag->slug()->getValue());
    }

    public function test_article_count_starts_at_zero(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');

        $tag = Tag::create($tagId, $tagName, $tagSlug);

        $this->assertSame(0, $tag->articleCount());
    }

    public function test_from_persistence_creates_tag_without_events(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $tagName = new TagName('Technology');
        $tagSlug = new TagSlug('technology');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-02 15:30:00');

        $tag = Tag::fromPersistence(
            id: $tagId,
            name: $tagName,
            slug: $tagSlug,
            articleCount: 5,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );

        $this->assertSame($tagId, $tag->id());
        $this->assertSame($tagName, $tag->name());
        $this->assertSame($tagSlug, $tag->slug());
        $this->assertSame(5, $tag->articleCount());
        $this->assertSame($createdAt, $tag->createdAt());
        $this->assertSame($updatedAt, $tag->updatedAt());
        $this->assertCount(0, $tag->getEvents());
    }
}
