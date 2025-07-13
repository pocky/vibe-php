<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\CreateArticle;

use App\BlogContext\Domain\CreateArticle\Creator;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class CreatorTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private ArticleRepositoryInterface $repository;
    private Creator $creator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->creator = new Creator($this->repository);
    }

    public function testCreateArticleSuccessfully(): void
    {
        // Given
        $articleIdValue = $this->generateArticleId()->getValue();
        $articleId = new ArticleId($articleIdValue);
        $title = new Title('My Great Article Title');
        $content = new Content('This is the article content with sufficient text.');
        $slug = new Slug('my-great-article-title');
        $status = ArticleStatus::DRAFT;
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $this->repository
            ->expects($this->once())
            ->method('existsBySlug')
            ->with($this->callback(fn ($slug) => $slug instanceof Slug && 'my-great-article-title' === $slug->getValue()))
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($article) => $article->title->equals(new Title('My Great Article Title'))
                && $article->content->equals(new Content('This is the article content with sufficient text.'))
                && 'my-great-article-title' === $article->slug->getValue()
                && ArticleStatus::DRAFT === $article->status
                && $article->hasUnreleasedEvents()));

        // When
        $article = ($this->creator)($articleId, $title, $content, $slug, $status, $createdAt);

        // Then
        $this->assertSame($articleIdValue, $article->id->getValue());
        $this->assertSame('my-great-article-title', $article->slug->getValue());
        $this->assertTrue($article->status->isDraft());
        $this->assertTrue($article->hasUnreleasedEvents()); // Events available for Application layer
        $this->assertSame($createdAt, $article->createdAt);
    }

    public function testCreateArticleThrowsExceptionWhenSlugExists(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440001');
        $title = new Title('Existing Article Title');
        $content = new Content('Content for this existing article.');
        $slug = new Slug('existing-article-title');
        $status = ArticleStatus::DRAFT;
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $this->repository
            ->method('existsBySlug')
            ->willReturn(true);

        // Then
        $this->expectException(ArticleAlreadyExists::class);

        // When
        ($this->creator)($articleId, $title, $content, $slug, $status, $createdAt);
    }

    public function testCreateArticleWithDifferentIds(): void
    {
        // Given
        $articleId1 = new ArticleId('550e8400-e29b-41d4-a716-446655440002');
        $articleId2 = new ArticleId('550e8400-e29b-41d4-a716-446655440003');
        $title = new Title('Unique Article Title');
        $content = new Content('Content for this unique article.');
        $slug1 = new Slug('unique-article-title-1');
        $slug2 = new Slug('unique-article-title-2');
        $status = ArticleStatus::DRAFT;
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $this->repository->method('existsBySlug')->willReturn(false);
        $this->repository->method('save');

        // When
        $article1 = ($this->creator)($articleId1, $title, $content, $slug1, $status, $createdAt);
        $article2 = ($this->creator)($articleId2, $title, $content, $slug2, $status, $createdAt);

        // Then
        $this->assertNotEquals($article1->id->getValue(), $article2->id->getValue());
    }

    public function testCreateArticleEmitsDomainEvent(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440004');
        $title = new Title('Event Article Title');
        $content = new Content('Content for article with events.');
        $slug = new Slug('event-article-title');
        $status = ArticleStatus::DRAFT;
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $this->repository->method('existsBySlug')->willReturn(false);
        $this->repository->method('save');

        // When
        $article = ($this->creator)($articleId, $title, $content, $slug, $status, $createdAt);

        // Then - Events are available for Application layer to dispatch
        $this->assertTrue($article->hasUnreleasedEvents());
        $events = $article->releaseEvents();
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertSame($article->id->getValue(), $event->articleId()->getValue());
        $this->assertSame($title->getValue(), $event->title()->getValue());
        $this->assertSame($createdAt, $event->createdAt());
    }

    public function testCreateArticleWithSpecialCharactersInTitle(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440005');
        $title = new Title('Article with Special @#$%! Characters');
        $content = new Content('Content for article with special characters in title.');
        $slug = new Slug('article-with-special-characters');
        $status = ArticleStatus::DRAFT;
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $this->repository->method('existsBySlug')->willReturn(false);
        $this->repository->method('save');

        // When
        $article = ($this->creator)($articleId, $title, $content, $slug, $status, $createdAt);

        // Then
        $this->assertSame('article-with-special-characters', $article->slug->getValue());
    }
}
