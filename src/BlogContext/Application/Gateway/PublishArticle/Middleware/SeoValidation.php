<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Middleware;

use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\Application\Gateway\PublishArticle\Request;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotFound;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotReady;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class SeoValidation
{
    public function __construct(
        private GetArticleGateway $getArticleGateway,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */

        // Get the article to validate
        try {
            $getRequest = GetArticleRequest::fromData([
                'id' => $request->articleId,
            ]);
            $getResponse = ($this->getArticleGateway)($getRequest);
            $articleData = $getResponse->data();
        } catch (\Throwable) {
            // If article doesn't exist, let the domain handle it
            // Re-throw as ArticleNotFound for consistency
            throw new ArticleNotFound(new ArticleId($request->articleId));
        }

        // Check if article is already published - if so, skip SEO validation
        if ('published' === $articleData['status']) {
            // Article is already published, let the domain handle this case
            /** @var GatewayResponse */
            return $next($request);
        }

        // SEO validation rules
        $errors = [];

        // Title validation
        if (10 > strlen((string) $articleData['title'])) {
            $errors[] = 'Title must be at least 10 characters for SEO';
        }
        if (60 < strlen((string) $articleData['title'])) {
            $errors[] = 'Title should not exceed 60 characters for optimal SEO';
        }

        // Content validation
        if (50 > strlen((string) $articleData['content'])) {
            $errors[] = 'Content must be at least 50 characters';
        }

        // Slug validation
        if (3 > strlen((string) $articleData['slug'])) {
            $errors[] = 'Slug must be at least 3 characters';
        }

        if ([] !== $errors) {
            throw new ArticleNotReady(new ArticleId($request->articleId), 'Article does not meet SEO requirements: ' . implode(', ', $errors));
        }

        /** @var GatewayResponse */
        return $next($request);
    }
}
