<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\GetArticle;

use App\BlogContext\Application\Gateway\GetArticle\Gateway;
use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\BlogContext\Domain\Shared\Repository\ArticleData;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
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
        $articleData = new ArticleData(
            id: new ArticleId($articleId->getValue()),
            title: new Title('Test Article'),
            content: new Content('Test content'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED,
            createdAt: new \DateTimeImmutable('2024-01-01T12:00:00+00:00'),
            publishedAt: new \DateTimeImmutable('2024-01-01T13:00:00+00:00'),
            updatedAt: new \DateTimeImmutable('2024-01-01T12:00:00+00:00')
        );

        $middleware = new readonly class($articleData) {
            public function __construct(
                private ArticleData $articleData
            ) {
            }

            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                return new Response([
                    'id' => $this->articleData->id->toString(),
                    'title' => $this->articleData->title->getValue(),
                    'content' => $this->articleData->content->getValue(),
                    'slug' => $this->articleData->slug->getValue(),
                    'status' => $this->articleData->status->value,
                    'created_at' => $this->articleData->createdAt->format(\DateTimeInterface::ATOM),
                    'updated_at' => $this->articleData->updatedAt->format(\DateTimeInterface::ATOM),
                    'published_at' => $this->articleData->publishedAt?->format(\DateTimeInterface::ATOM),
                ]);
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'id' => $articleId->getValue(),
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertEquals($articleId->getValue(), $responseData['id']);
        $this->assertEquals('Test Article', $responseData['title']);
        $this->assertEquals('Test content', $responseData['content']);
        $this->assertEquals('test-article', $responseData['slug']);
        $this->assertEquals('published', $responseData['status']);
    }
}
