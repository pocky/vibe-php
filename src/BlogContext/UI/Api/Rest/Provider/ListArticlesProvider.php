<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BlogContext\Application\Gateway\ListArticles\Gateway as ListArticlesGateway;
use App\BlogContext\Application\Gateway\ListArticles\Request as ListArticlesRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;

final readonly class ListArticlesProvider implements ProviderInterface
{
    public function __construct(
        private ListArticlesGateway $listArticlesGateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filters = $context['filters'] ?? [];

        $request = ListArticlesRequest::fromData([
            'page' => (int) (is_array($filters) && isset($filters['page']) ? $filters['page'] : 1),
            'limit' => (int) (is_array($filters) && isset($filters['itemsPerPage']) ? $filters['itemsPerPage'] : 20),
            'status' => is_array($filters) && isset($filters['status']) ? $filters['status'] : null,
        ]);

        $response = ($this->listArticlesGateway)($request);

        $data = $response->data();
        $articles = $data['articles'] ?? [];

        return array_map(
            fn (array $article) => $this->transformToResource($article),
            $articles
        );
    }

    private function transformToResource(array $data): ArticleResource
    {
        return new ArticleResource(
            id: $data['id'],
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'],
            status: $data['status'],
            publishedAt: isset($data['published_at']) && $data['published_at']
                ? new \DateTimeImmutable($data['published_at'])
                : null,
        );
    }
}
