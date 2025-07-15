<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\UpdateArticle;

use App\BlogContext\Application\Gateway\UpdateArticle\Response;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testDataReturnsCorrectArray(): void
    {
        // Given
        $articleId = $this->generateArticleId()->getValue();
        $updatedAt = new \DateTimeImmutable('2024-01-15 10:30:00');
        $response = new Response(
            articleId: $articleId,
            title: 'Updated Article Title',
            content: 'This is the updated content of the article.',
            slug: 'updated-article-title',
            status: 'published',
            updatedAt: $updatedAt,
        );

        // When
        $data = $response->data();

        // Then
        $this->assertEquals([
            'articleId' => $articleId,
            'title' => 'Updated Article Title',
            'content' => 'This is the updated content of the article.',
            'slug' => 'updated-article-title',
            'status' => 'published',
            'updatedAt' => $updatedAt->format(\DateTimeInterface::ATOM),
        ], $data);
    }

    public function testConstructorAssignsPropertiesCorrectly(): void
    {
        // Given
        $articleId = $this->generateArticleId()->getValue();
        $updatedAt = new \DateTimeImmutable();

        // When
        $response = new Response(
            articleId: $articleId,
            title: 'Test Title',
            content: 'Test Content',
            slug: 'test-title',
            status: 'draft',
            updatedAt: $updatedAt,
        );

        // Then
        $this->assertEquals($articleId, $response->articleId);
        $this->assertEquals('Test Title', $response->title);
        $this->assertEquals('Test Content', $response->content);
        $this->assertEquals('test-title', $response->slug);
        $this->assertEquals('draft', $response->status);
        $this->assertSame($updatedAt, $response->updatedAt);
    }
}
