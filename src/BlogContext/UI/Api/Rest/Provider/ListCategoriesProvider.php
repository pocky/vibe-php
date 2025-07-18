<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BlogContext\Application\Gateway\ListCategories\Gateway as ListCategoriesGateway;
use App\BlogContext\Application\Gateway\ListCategories\Request as ListCategoriesRequest;
use App\BlogContext\UI\Api\Rest\Resource\CategoryResource;

final readonly class ListCategoriesProvider implements ProviderInterface
{
    public function __construct(
        private ListCategoriesGateway $listCategoriesGateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filters = $context['filters'] ?? [];

        $request = ListCategoriesRequest::fromData([
            'page' => (int) ($filters['page'] ?? 1),
            'limit' => (int) ($filters['itemsPerPage'] ?? 20),
            // TODO: Add filter parameters
            // 'status' => $filters['status'] ?? null,
            // 'search' => $filters['search'] ?? null,
        ]);

        $response = ($this->listCategoriesGateway)($request);

        $data = $response->data();
        $categories = $data['categories'] ?? [];

        return array_map(
            fn (array $category) => $this->transformToResource($category),
            $categories
        );
    }

    private function transformToResource(array $data): CategoryResource
    {
        return new CategoryResource(
            id: $data['id'] ?? null,
            // TODO: Map other properties
            // title: $data['title'] ?? null,
            // content: $data['content'] ?? null,
            createdAt: isset($data['created_at']) && $data['created_at']
                ? new \DateTimeImmutable($data['created_at'])
                : null,
            updatedAt: isset($data['updated_at']) && $data['updated_at']
                ? new \DateTimeImmutable($data['updated_at'])
                : null,
        );
    }
}
