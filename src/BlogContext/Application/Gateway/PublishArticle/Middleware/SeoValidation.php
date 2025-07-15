<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Middleware;

use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\Application\Gateway\PublishArticle\Exception\SeoValidationException;
use App\BlogContext\Application\Gateway\PublishArticle\Request;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotFound;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SeoValidation
{
    public function __construct(
        private GetArticleGateway $getArticleGateway,
        private TranslatorInterface $translator,
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
        $titleLength = strlen((string) $articleData['title']);
        $contentLength = strlen((string) $articleData['content']);
        $slugLength = strlen((string) $articleData['slug']);

        // Title validation
        if (10 > $titleLength) {
            throw SeoValidationException::titleTooShort(10, $titleLength, $this->translator);
        }

        // Content validation
        if (50 > $contentLength) {
            throw SeoValidationException::contentTooShort(50, $contentLength, $this->translator);
        }

        // We could add more SEO validations here
        // For example: missing meta description
        if (!isset($articleData['meta_description']) || empty($articleData['meta_description'])) {
            throw SeoValidationException::missingMetaDescription($this->translator);
        }

        /** @var GatewayResponse */
        return $next($request);
    }
}
