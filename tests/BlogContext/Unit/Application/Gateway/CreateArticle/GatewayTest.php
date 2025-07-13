<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateArticle;

use App\BlogContext\Application\Gateway\CreateArticle\Gateway;
use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
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
        $generatedArticleId = $this->generateArticleId();
        $generatedAuthorId = $this->generateArticleId(); // Using same generator for consistency

        $middleware = new readonly class($generatedArticleId->getValue()) {
            public function __construct(
                private string $articleIdToReturn
            ) {
            }

            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                return new Response(
                    articleId: $this->articleIdToReturn,
                    slug: $request->slug,
                    status: $request->status,
                    createdAt: new \DateTimeImmutable(),
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'title' => 'New Article',
            'content' => 'This is the content of the new article.',
            'slug' => 'new-article',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00+00:00',
            'authorId' => $generatedAuthorId->getValue(),
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertArrayHasKey('articleId', $responseData);
        $this->assertEquals('new-article', $responseData['slug']);
        $this->assertEquals('draft', $responseData['status']);
        $this->assertArrayHasKey('createdAt', $responseData);
    }
}
