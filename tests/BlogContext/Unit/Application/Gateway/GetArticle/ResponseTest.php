<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\GetArticle;

use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    public function testResponseCreationWithArticleData(): void
    {
        $articleData = [
            'id' => $this->generateArticleId()->getValue(),
            'title' => 'Test Article',
            'content' => 'Test content',
            'slug' => 'test-article',
            'status' => 'published',
            'created_at' => '2024-01-01T12:00:00+00:00',
            'updated_at' => '2024-01-01T12:00:00+00:00',
            'published_at' => '2024-01-01T13:00:00+00:00',
        ];

        $response = new Response($articleData);

        $this->assertEquals($articleData, $response->data());
    }

    public function testResponseGetters(): void
    {
        $articleData = [
            'id' => $this->generateArticleId()->getValue(),
            'title' => 'Test Article',
            'slug' => 'test-article',
        ];

        $response = new Response($articleData);

        $this->assertEquals($articleData['id'], $response->article['id']);
        $this->assertEquals($articleData['title'], $response->article['title']);
        $this->assertEquals($articleData['slug'], $response->article['slug']);
    }

    public function testResponseIsReadonly(): void
    {
        $response = new Response([
            'id' => $this->generateArticleId()->getValue(),
        ]);

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $response->article = [];
    }
}
