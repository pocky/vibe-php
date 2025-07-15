<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Provider;

use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\UI\Web\Admin\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Psr\Log\LoggerInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class ArticleItemProvider implements ProviderInterface
{
    public function __construct(
        private GetArticleGateway $getArticleGateway,
        private LoggerInterface $logger,
    ) {
    }

    public function provide(Operation $operation, Context $context): object|null
    {
        $request = $context->get(RequestOption::class)?->request();
        if (!$request) {
            $this->logger->error('No request in context');

            return null;
        }

        $articleId = $request->attributes->get('id');
        if (!$articleId) {
            $this->logger->error('No article ID in request attributes');

            return null;
        }

        $this->logger->info('ArticleItemProvider called', [
            'articleId' => $articleId,
        ]);

        try {
            $gatewayRequest = GetArticleRequest::fromData([
                'id' => $articleId,
            ]);

            $gatewayResponse = ($this->getArticleGateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // The gateway returns the article data directly, not in a sub-array
            $resource = $this->transformToResource($responseData);
            $this->logger->info('Article resource created successfully', [
                'resource' => $resource,
            ]);

            return $resource;
        } catch (GatewayException|\RuntimeException $e) {
            $this->logger->error('Error in ArticleItemProvider', [
                'error' => $e->getMessage(),
            ]);
            if (str_contains($e->getMessage(), 'not found')) {
                return null;
            }
            throw $e;
        }
    }

    private function transformToResource(array $data): ArticleResource
    {
        return new ArticleResource(
            id: $data['id'] ?? null,
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
            status: $data['status'] ?? null,
            createdAt: isset($data['created_at']) && $data['created_at']
                ? new \DateTimeImmutable($data['created_at'])
                : null,
            updatedAt: isset($data['updated_at']) && $data['updated_at']
                ? new \DateTimeImmutable($data['updated_at'])
                : null,
            publishedAt: isset($data['published_at']) && $data['published_at']
                ? new \DateTimeImmutable($data['published_at'])
                : null,
        );
    }
}
