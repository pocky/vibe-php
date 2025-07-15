<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\ApproveArticle;

use App\BlogContext\Application\Gateway\ApproveArticle\Gateway;
use App\BlogContext\Application\Gateway\ApproveArticle\Request;
use App\BlogContext\Application\Gateway\ApproveArticle\Response;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use PHPUnit\Framework\TestCase;

final class GatewayTest extends TestCase
{
    public function testApproveArticleWithReason(): void
    {
        // Given
        $middleware = new readonly class {
            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                /** @var Request $request */
                return new Response(
                    articleId: $request->articleId,
                    status: 'approved',
                    reviewerId: $request->reviewerId,
                    reviewedAt: new \DateTimeImmutable(),
                    approvalReason: $request->reason,
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => 'Excellent article, ready for publication',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);

        $responseData = $response->data();
        $this->assertArrayHasKey('articleId', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('reviewerId', $responseData);
        $this->assertArrayHasKey('reviewedAt', $responseData);
        $this->assertArrayHasKey('approvalReason', $responseData);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $responseData['articleId']);
        $this->assertSame('approved', $responseData['status']);
        $this->assertSame('770e8400-e29b-41d4-a716-446655440002', $responseData['reviewerId']);
        $this->assertSame('Excellent article, ready for publication', $responseData['approvalReason']);
        $this->assertNotNull($responseData['reviewedAt']);
    }

    public function testApproveArticleWithoutReason(): void
    {
        // Given
        $middleware = new readonly class {
            public function __invoke(GatewayRequest $request): GatewayResponse
            {
                /** @var Request $request */
                return new Response(
                    articleId: $request->articleId,
                    status: 'approved',
                    reviewerId: $request->reviewerId,
                    reviewedAt: new \DateTimeImmutable(),
                    approvalReason: $request->reason,
                );
            }
        };

        $gateway = new Gateway([$middleware]);

        $request = Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
        ]);

        // When
        $response = $gateway($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);

        $responseData = $response->data();
        $this->assertNull($responseData['approvalReason']);
    }
}
