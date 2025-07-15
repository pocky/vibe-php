<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\AutoSaveArticle;

use App\BlogContext\Application\Gateway\AutoSaveArticle\Request;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testFromDataCreatesRequestSuccessfully(): void
    {
        // Given
        $articleId = $this->generateArticleId();
        $data = [
            'articleId' => $articleId->getValue(),
            'title' => 'Test Article Title',
            'content' => 'This is the content of the test article.',
        ];

        // When
        $request = Request::fromData($data);

        // Then
        $this->assertEquals($articleId->getValue(), $request->articleId);
        $this->assertEquals('Test Article Title', $request->title);
        $this->assertEquals('This is the content of the test article.', $request->content);
    }

    public function testFromDataThrowsExceptionWhenArticleIdIsMissing(): void
    {
        // Given
        $data = [
            'title' => 'Test Article Title',
            'content' => 'This is the content of the test article.',
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
        $articleId = $this->generateArticleId();
        $request = new Request(
            articleId: $articleId->getValue(),
            title: 'Test Article Title',
            content: 'This is the content of the test article.',
        );

        // When
        $data = $request->data();

        // Then
        $this->assertEquals([
            'articleId' => $articleId->getValue(),
            'title' => 'Test Article Title',
            'content' => 'This is the content of the test article.',
        ], $data);
    }
}
