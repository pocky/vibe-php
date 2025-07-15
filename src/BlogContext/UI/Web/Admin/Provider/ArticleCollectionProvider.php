<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Provider;

use App\BlogContext\Application\Gateway\ListArticles\Gateway as ListArticlesGateway;
use App\BlogContext\Application\Gateway\ListArticles\Request as ListArticlesRequest;
use App\BlogContext\UI\Web\Admin\Resource\ArticleResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class ArticleCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ListArticlesGateway $listArticlesGateway,
    ) {
    }

    /**
     * @return array<ArticleResource>
     */
    public function provide(Operation $operation, Context $context): array
    {
        $request = $context->get(RequestOption::class)?->request();

        // Get pagination parameters from request
        $page = $request ? (int) $request->query->get('page', 1) : 1;
        $limit = $request ? (int) $request->query->get('limit', 20) : 20;

        // Create gateway request
        $gatewayRequest = ListArticlesRequest::fromData([
            'page' => $page,
            'limit' => $limit,
        ]);

        // Execute gateway
        $gatewayResponse = ($this->listArticlesGateway)($gatewayRequest);
        $responseData = $gatewayResponse->data();

        // Transform response to ArticleResource objects
        /** @var array<ArticleResource> $articles */
        $articles = [];
        if (isset($responseData['articles']) && is_array($responseData['articles'])) {
            foreach ($responseData['articles'] as $articleData) {
                if (is_array($articleData)) {
                    $articles[] = $this->transformToResource($articleData);
                }
            }
        }

        return $articles;
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
