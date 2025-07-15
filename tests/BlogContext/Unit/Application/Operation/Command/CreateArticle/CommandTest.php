<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Application\Operation\Command\CreateArticle;

use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testCreateCommandWithValidData(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440001');
        $title = 'My First Article';
        $content = 'This is the content of my first article.';
        $slug = 'my-first-article';
        $status = 'draft';
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');
        $authorId = $this->generateArticleId()->getValue();

        // When
        $command = new Command(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            createdAt: $createdAt,
            authorId: $authorId
        );

        // Then
        $this->assertSame($articleId, $command->articleId);
        $this->assertSame($title, $command->title);
        $this->assertSame($content, $command->content);
        $this->assertSame($slug, $command->slug);
        $this->assertSame($status, $command->status);
        $this->assertSame($createdAt, $command->createdAt);
        $this->assertSame($authorId, $command->authorId);
    }

    public function testCreateCommandWithoutAuthor(): void
    {
        // Given
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440002');
        $title = 'My First Article';
        $content = 'This is the content of my first article.';
        $slug = 'my-first-article';
        $status = 'draft';
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00Z');

        // When
        $command = new Command(
            articleId: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: $status,
            createdAt: $createdAt,
            authorId: null
        );

        // Then
        $this->assertSame($articleId, $command->articleId);
        $this->assertSame($title, $command->title);
        $this->assertSame($content, $command->content);
        $this->assertSame($slug, $command->slug);
        $this->assertSame($status, $command->status);
        $this->assertSame($createdAt, $command->createdAt);
        $this->assertNull($command->authorId);
    }
}
