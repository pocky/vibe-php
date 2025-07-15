<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\SubmitForReview;

use App\BlogContext\Application\Gateway\SubmitForReview\Gateway;
use App\BlogContext\Application\Gateway\SubmitForReview\Request;
use App\BlogContext\Application\Gateway\SubmitForReview\Response;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use PHPUnit\Framework\TestCase;

final class GatewayTest extends TestCase
{
    public function testSuccessfulSubmission(): void
    {
        // Given
        $middleware = new readonly class {
            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                /** @var Request $request */
                return new Response(
                    articleId: $request->articleId,
                    status: 'pending_review',
                    submittedAt: new \DateTimeImmutable(),
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'authorId' => '660e8400-e29b-41d4-a716-446655440001',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);

        $responseData = $response->data();
        $this->assertArrayHasKey('articleId', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('submittedAt', $responseData);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $responseData['articleId']);
        $this->assertSame('pending_review', $responseData['status']);
        $this->assertNotNull($responseData['submittedAt']);
    }

    public function testMissingArticleIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([
            'authorId' => '660e8400-e29b-41d4-a716-446655440001',
        ]);
    }

    public function testInvalidArticleIdFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid article ID format');

        Request::fromData([
            'articleId' => 'invalid-uuid',
            'authorId' => '660e8400-e29b-41d4-a716-446655440001',
        ]);
    }

    public function testOptionalAuthorId(): void
    {
        // Given
        $middleware = new readonly class {
            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                /** @var Request $request */
                return new Response(
                    articleId: $request->articleId,
                    status: 'pending_review',
                    submittedAt: new \DateTimeImmutable(),
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $responseData = $response->data();
        $this->assertArrayHasKey('articleId', $responseData);
        $this->assertNull($request->authorId); // Verify authorId is null
    }
}
