<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\ListArticles;

use App\BlogContext\Application\Gateway\ListArticles\Gateway;
use App\BlogContext\Application\Gateway\ListArticles\Request;
use App\BlogContext\Application\Gateway\ListArticles\Response;
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
        $articles = [
            [
                'id' => $this->generateArticleId()->getValue(),
                'title' => 'Article 1',
                'slug' => 'article-1',
                'status' => 'published',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'title' => 'Article 2',
                'slug' => 'article-2',
                'status' => 'published',
            ],
        ];

        $result = [
            'articles' => $articles,
            'total' => 25,
            'page' => 1,
            'limit' => 10,
            'hasNextPage' => true,
        ];

        $middleware = new readonly class($result) {
            public function __construct(
                private array $result
            ) {
            }

            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                return new Response(
                    articles: $this->result['articles'],
                    total: $this->result['total'],
                    page: $this->result['page'],
                    limit: $this->result['limit'],
                    hasNextPage: $this->result['hasNextPage'],
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'page' => 1,
            'limit' => 10,
            'status' => 'published',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertArrayHasKey('articles', $responseData);
        $this->assertCount(2, $responseData['articles']);
        $this->assertEquals(25, $responseData['total']);
        $this->assertEquals(1, $responseData['page']);
        $this->assertEquals(10, $responseData['limit']);
        $this->assertTrue($responseData['has_next_page']);
    }
}
