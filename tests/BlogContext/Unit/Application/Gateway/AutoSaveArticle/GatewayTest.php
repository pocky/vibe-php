<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\AutoSaveArticle;

use App\BlogContext\Application\Gateway\AutoSaveArticle\Gateway;
use App\BlogContext\Application\Gateway\AutoSaveArticle\Request;
use App\BlogContext\Application\Gateway\AutoSaveArticle\Response;
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
                    slug: 'auto-saved-slug',
                    status: 'draft',
                    autoSavedAt: new \DateTimeImmutable(),
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => $articleId->getValue(),
            'title' => 'Auto-saved Title',
            'content' => 'Auto-saved content for the article',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertEquals($articleId->getValue(), $responseData['articleId']);
        $this->assertEquals('Auto-saved Title', $responseData['title']);
        $this->assertEquals('Auto-saved content for the article', $responseData['content']);
        $this->assertEquals('auto-saved-slug', $responseData['slug']);
        $this->assertEquals('draft', $responseData['status']);
        $this->assertArrayHasKey('autoSavedAt', $responseData);
    }
}
