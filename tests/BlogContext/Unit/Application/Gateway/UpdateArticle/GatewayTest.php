<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\UpdateArticle;

use App\BlogContext\Application\Gateway\UpdateArticle\Gateway;
use App\BlogContext\Application\Gateway\UpdateArticle\Request;
use App\BlogContext\Application\Gateway\UpdateArticle\Response;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class GatewayTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testInvokeExecutesSuccessfully(): void
    {
        // Given
        $articleId = $this->generateArticleId();
        $middleware = new class {
            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                return new Response(
                    articleId: $request->articleId,
                    title: $request->title,
                    content: $request->content,
                    slug: $request->slug,
                    status: $request->status,
                    updatedAt: new \DateTimeImmutable(),
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => $articleId->getValue(),
            'title' => 'Updated Title',
            'content' => 'Updated content for the article',
            'slug' => 'updated-title',
            'status' => 'published',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertEquals($articleId->getValue(), $responseData['articleId']);
        $this->assertEquals('Updated Title', $responseData['title']);
        $this->assertEquals('Updated content for the article', $responseData['content']);
        $this->assertArrayHasKey('updatedAt', $responseData);
    }
}
