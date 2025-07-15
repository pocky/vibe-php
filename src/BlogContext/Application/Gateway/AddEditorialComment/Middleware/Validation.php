<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AddEditorialComment\Middleware;

use App\BlogContext\Application\Gateway\AddEditorialComment\Request;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Validation
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Verify article exists
        $article = $this->articleRepository->findById(new ArticleId($request->articleId));
        if (!$article instanceof \App\BlogContext\Domain\Shared\Model\Article) {
            throw new \RuntimeException('Article not found');
        }

        // Additional business validation could go here
        // For example: check if reviewer has permission to comment

        /** @var GatewayResponse */
        return $next($request);
    }
}
