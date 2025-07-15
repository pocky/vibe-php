<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\UpdateArticle;

use App\BlogContext\Application\Gateway\UpdateArticle\Request;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testFromDataCreatesRequestSuccessfully(): void
    {
        // Given
        $articleId = $this->generateArticleId()->getValue();
        $data = [
            'articleId' => $articleId,
            'title' => 'Test Article Title',
            'content' => 'This is the content of the test article.',
            'slug' => 'test-article-title',
            'status' => 'draft',
        ];

        // When
        $request = Request::fromData($data);

        // Then
        $this->assertEquals($articleId, $request->articleId);
        $this->assertEquals('Test Article Title', $request->title);
        $this->assertEquals('This is the content of the test article.', $request->content);
        $this->assertEquals('test-article-title', $request->slug);
        $this->assertEquals('draft', $request->status);
    }

    public function testFromDataThrowsExceptionWhenArticleIdIsMissing(): void
    {
        // Given
        $data = [
            'title' => 'Test Article Title',
            'content' => 'This is the content of the test article.',
            'slug' => 'test-article-title',
            'status' => 'draft',
        ];

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        // When
        Request::fromData($data);
    }

    public function testFromDataThrowsExceptionWhenTitleIsMissing(): void
    {
        // Given
        $data = [
            'articleId' => $this->generateArticleId()->getValue(),
            'content' => 'This is the content of the test article.',
            'slug' => 'test-article-title',
            'status' => 'draft',
        ];

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title is required');

        // When
        Request::fromData($data);
    }

    public function testFromDataThrowsExceptionWhenContentIsMissing(): void
    {
        // Given
        $data = [
            'articleId' => $this->generateArticleId()->getValue(),
            'title' => 'Test Article Title',
            'slug' => 'test-article-title',
            'status' => 'draft',
        ];

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Content is required');

        // When
        Request::fromData($data);
    }

    public function testDataReturnsCorrectArray(): void
    {
        // Given
        $articleId = $this->generateArticleId()->getValue();
        $request = new Request(
            articleId: $articleId,
            title: 'Test Article Title',
            content: 'This is the content of the test article.',
            slug: 'test-article-title',
            status: 'draft',
        );

        // When
        $data = $request->data();

        // Then
        $this->assertEquals([
            'articleId' => $articleId,
            'title' => 'Test Article Title',
            'content' => 'This is the content of the test article.',
            'slug' => 'test-article-title',
            'status' => 'draft',
        ], $data);
    }
}
