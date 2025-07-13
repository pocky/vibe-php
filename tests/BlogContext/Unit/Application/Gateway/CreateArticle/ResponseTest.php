<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateArticle;

use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testResponseCreationWithTypedProperties(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $slug = 'created-article';
        $status = 'draft';
        $createdAt = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');

        $response = new Response(
            articleId: $articleId,
            slug: $slug,
            status: $status,
            createdAt: $createdAt
        );

        $this->assertEquals($articleId, $response->articleId);
        $this->assertEquals($slug, $response->slug);
        $this->assertEquals($status, $response->status);
        $this->assertEquals($createdAt, $response->createdAt);
    }

    public function testResponseDataMethod(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $slug = 'created-article';
        $status = 'draft';
        $createdAt = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');

        $response = new Response(
            articleId: $articleId,
            slug: $slug,
            status: $status,
            createdAt: $createdAt
        );

        $expected = [
            'articleId' => $articleId,
            'slug' => $slug,
            'status' => $status,
            'createdAt' => $createdAt->format(\DateTimeInterface::ATOM),
        ];

        $this->assertEquals($expected, $response->data());
    }

    public function testResponseWithPublishedStatus(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $slug = 'published-article';
        $status = 'published';
        $createdAt = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');

        $response = new Response(
            articleId: $articleId,
            slug: $slug,
            status: $status,
            createdAt: $createdAt
        );

        $this->assertEquals('published', $response->status);
    }

    public function testResponseIsReadonly(): void
    {
        $response = new Response(
            articleId: $this->generateArticleId()->getValue(),
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable()
        );

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $response->articleId = 'new-id';
    }
}
