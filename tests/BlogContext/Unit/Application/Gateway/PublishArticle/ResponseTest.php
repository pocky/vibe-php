<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\PublishArticle;

use App\BlogContext\Application\Gateway\PublishArticle\Response;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testResponseCreationWithTypedProperties(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $status = 'published';
        $publishedAt = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');

        $response = new Response(
            articleId: $articleId,
            status: $status,
            publishedAt: $publishedAt
        );

        $this->assertEquals($articleId, $response->articleId);
        $this->assertEquals($status, $response->status);
        $this->assertEquals($publishedAt, $response->publishedAt);
    }

    public function testResponseDataMethod(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $status = 'published';
        $publishedAt = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');

        $response = new Response(
            articleId: $articleId,
            status: $status,
            publishedAt: $publishedAt
        );

        $expected = [
            'articleId' => $articleId,
            'status' => $status,
            'publishedAt' => $publishedAt->format(\DateTimeInterface::ATOM),
        ];

        $this->assertEquals($expected, $response->data());
    }

    public function testResponseIsReadonly(): void
    {
        $response = new Response(
            articleId: $this->generateArticleId()->getValue(),
            status: 'published',
            publishedAt: new \DateTimeImmutable()
        );

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $response->articleId = 'new-id';
    }
}
