<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\AutoSaveArticle;

use App\BlogContext\Application\Gateway\AutoSaveArticle\Response;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testDataReturnsCorrectArray(): void
    {
        // Given
        $articleId = $this->generateArticleId();
        $autoSavedAt = new \DateTimeImmutable('2024-01-15 10:30:00');
        $response = new Response(
            articleId: $articleId->getValue(),
            title: 'Auto-saved Article Title',
            content: 'This is the auto-saved content of the article.',
            autoSavedAt: $autoSavedAt,
        );

        // When
        $data = $response->data();

        // Then
        $this->assertEquals([
            'articleId' => $articleId->getValue(),
            'title' => 'Auto-saved Article Title',
            'content' => 'This is the auto-saved content of the article.',
            'autoSavedAt' => $autoSavedAt->format(\DateTimeInterface::ATOM),
        ], $data);
    }

    public function testConstructorAssignsPropertiesCorrectly(): void
    {
        // Given
        $articleId = $this->generateArticleId();
        $autoSavedAt = new \DateTimeImmutable();

        // When
        $response = new Response(
            articleId: $articleId->getValue(),
            title: 'Test Title',
            content: 'Test Content',
            autoSavedAt: $autoSavedAt,
        );

        // Then
        $this->assertEquals($articleId->getValue(), $response->articleId);
        $this->assertEquals('Test Title', $response->title);
        $this->assertEquals('Test Content', $response->content);
        $this->assertSame($autoSavedAt, $response->autoSavedAt);
    }
}
