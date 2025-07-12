<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use PHPUnit\Framework\TestCase;

final class ArticleStatusTest extends TestCase
{
    public function testDraftStatus(): void
    {
        $status = ArticleStatus::DRAFT;

        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('draft', $status->getValue());
    }

    public function testPublishedStatus(): void
    {
        $status = ArticleStatus::PUBLISHED;

        $this->assertFalse($status->isDraft());
        $this->assertTrue($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('published', $status->getValue());
    }

    public function testArchivedStatus(): void
    {
        $status = ArticleStatus::ARCHIVED;

        $this->assertFalse($status->isDraft());
        $this->assertFalse($status->isPublished());
        $this->assertTrue($status->isArchived());
        $this->assertSame('archived', $status->getValue());
    }

    public function testCreateFromString(): void
    {
        $status = ArticleStatus::fromString('draft');
        $this->assertSame(ArticleStatus::DRAFT, $status);

        $status = ArticleStatus::fromString('published');
        $this->assertSame(ArticleStatus::PUBLISHED, $status);

        $status = ArticleStatus::fromString('archived');
        $this->assertSame(ArticleStatus::ARCHIVED, $status);
    }

    public function testCreateFromInvalidString(): void
    {
        $this->expectException(\ValueError::class);

        ArticleStatus::fromString('invalid');
    }

    public function testStatusEquality(): void
    {
        $status1 = ArticleStatus::DRAFT;
        $status2 = ArticleStatus::DRAFT;
        $status3 = ArticleStatus::PUBLISHED;

        $this->assertTrue($status1->equals($status2));
        $this->assertFalse($status1->equals($status3));
    }

    public function testGetAllCases(): void
    {
        $cases = ArticleStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ArticleStatus::DRAFT, $cases);
        $this->assertContains(ArticleStatus::PUBLISHED, $cases);
        $this->assertContains(ArticleStatus::ARCHIVED, $cases);
    }
}
