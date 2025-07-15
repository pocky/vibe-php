<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\GetArticle;

use App\BlogContext\Application\Gateway\GetArticle\Gateway;
use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\BlogContext\Domain\Shared\Model\Article;
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
        $article = new Article(
            id: new ArticleId($articleId->getValue()),
            title: new Title('Test Article'),
            content: new Content('Test content'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED,
            createdAt: new \DateTimeImmutable('2024-01-01T12:00:00+00:00'),
            updatedAt: new \DateTimeImmutable('2024-01-01T12:00:00+00:00'),
            publishedAt: new \DateTimeImmutable('2024-01-01T13:00:00+00:00')
        );

        $middleware = new readonly class($article) {
            public function __construct(
                private Article $article
            ) {
            }

            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                return new Response([
                    'id' => $this->article->getId()->toString(),
                    'title' => $this->article->getTitle()->getValue(),
                    'content' => $this->article->getContent()->getValue(),
                    'slug' => $this->article->getSlug()->getValue(),
                    'status' => $this->article->getStatus()->value,
                    'created_at' => $this->article->getCreatedAt()->format(\DateTimeInterface::ATOM),
                    'updated_at' => $this->article->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                    'published_at' => $this->article->getPublishedAt()?->format(\DateTimeInterface::ATOM),
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
