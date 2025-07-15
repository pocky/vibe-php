<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AddEditorialComment\Middleware;

use App\BlogContext\Application\Gateway\AddEditorialComment\Request;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Middleware\MiddlewareInterface;

final readonly class Validation implements MiddlewareInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    #[\Override]
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Verify article exists
        $articleData = $this->articleRepository->findById(new ArticleId($request->articleId));
        if (!$articleData instanceof \App\BlogContext\Domain\Shared\Repository\ArticleData) {
            throw new \RuntimeException('Article not found');
        }

        // Additional business validation could go here
        // For example: check if reviewer has permission to comment

        /** @var GatewayResponse */
        return $next($request);
    }
}
