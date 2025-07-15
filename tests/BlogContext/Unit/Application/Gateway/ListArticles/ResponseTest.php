<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\ListArticles;

use App\BlogContext\Application\Gateway\ListArticles\Response;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testResponseCreationWithArticlesData(): void
    {
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
                'status' => 'draft',
            ],
        ];

        $response = new Response(
            articles: $articles,
            total: 25,
            page: 1,
            limit: 20,
            hasNextPage: true
        );

        $responseData = $response->data();

        $this->assertArrayHasKey('articles', $responseData);
        $this->assertCount(2, $responseData['articles']);
        $this->assertEquals(25, $responseData['total']);
        $this->assertEquals(1, $responseData['page']);
        $this->assertTrue($responseData['has_next_page']);
    }

    public function testResponseGetters(): void
    {
        $articles = [[
            'id' => $this->generateArticleId()->getValue(),
        ]];

        $response = new Response(
            articles: $articles,
            total: 1,
            page: 1,
            limit: 20,
            hasNextPage: false
        );

        $this->assertEquals($articles, $response->getArticles());
        $this->assertEquals(1, $response->getTotal());
        $this->assertEquals(1, $response->getPage());
        $this->assertEquals(20, $response->getLimit());
        $this->assertFalse($response->hasNextPage());
    }

    public function testResponseIsReadonly(): void
    {
        $response = new Response(
            articles: [],
            total: 0,
            page: 1,
            limit: 20,
            hasNextPage: false
        );

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $response->articles = [];
    }
}
