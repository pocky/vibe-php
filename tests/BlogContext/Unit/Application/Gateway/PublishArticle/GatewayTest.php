<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\PublishArticle;

use App\BlogContext\Application\Gateway\PublishArticle\Gateway;
use App\BlogContext\Application\Gateway\PublishArticle\Request;
use App\BlogContext\Application\Gateway\PublishArticle\Response;
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
                    status: 'published',
                    publishedAt: new \DateTimeImmutable(),
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => $articleId->getValue(),
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertEquals($articleId->getValue(), $responseData['articleId']);
        $this->assertEquals('published', $responseData['status']);
        $this->assertArrayHasKey('publishedAt', $responseData);
    }
}
