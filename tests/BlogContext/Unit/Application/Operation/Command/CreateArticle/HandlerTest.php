<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Application\Operation\Command\CreateArticle;

use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Application\Operation\Command\CreateArticle\Handler;
use App\BlogContext\Domain\CreateArticle\CreatorInterface;
use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testHandleCreateArticleCommand(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440001');
        $slug = 'my-test-article';
        $status = 'draft';
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $command = new Command(
            articleId: $articleId,
            title: 'My Test Article',
            content: 'This is test content for the article.',
            slug: $slug,
            status: $status,
            createdAt: $createdAt,
            authorId: $this->generateArticleId()->getValue()
        );

        // Create a real Article instance for the test
        $articleInstance = new Article(
            id: $articleId,
            title: new Title('My Test Article'),
            content: new Content('This is test content for the article.'),
            slug: new Slug($slug),
            status: ArticleStatus::fromString($status),
            createdAt: $createdAt,
        );

        // Mock creator to return the article instance
        $creator = $this->createMock(CreatorInterface::class);
        $creator->expects($this->once())
            ->method('__invoke')
            ->willReturn($articleInstance);

        // Mock EventBus to verify event dispatch
        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->once())
            ->method('__invoke');

        $handler = new Handler($creator, $eventBus);

        // When
        ($handler)($command);

        // Then - verify creator and eventBus were called (already done with expects)
    }

    public function testHandleCreateArticleWithoutAuthor(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440002');
        $slug = 'article-without-author';
        $status = 'draft';
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        $command = new Command(
            articleId: $articleId,
            title: 'Article Without Author',
            content: 'Content without author.',
            slug: $slug,
            status: $status,
            createdAt: $createdAt,
            authorId: null
        );

        // Create a real Article instance for the test
        $articleInstance = new Article(
            id: $articleId,
            title: new Title('Article Without Author'),
            content: new Content('Content without author.'),
            slug: new Slug($slug),
            status: ArticleStatus::fromString($status),
            createdAt: $createdAt,
        );

        // Mock creator to return the article instance
        $creator = $this->createMock(CreatorInterface::class);
        $creator->expects($this->once())
            ->method('__invoke')
            ->willReturn($articleInstance);

        // Mock EventBus to verify event dispatch
        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->once())
            ->method('__invoke');

        $handler = new Handler($creator, $eventBus);

        // When
        ($handler)($command);

        // Then - verify creator and eventBus were called (already done with expects)
    }
}
