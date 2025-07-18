<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BlogContext\Application\Gateway\GetCategory\Gateway as GetCategoryGateway;
use App\BlogContext\Application\Gateway\GetCategory\Request as GetCategoryRequest;
use App\BlogContext\UI\Api\Rest\Resource\CategoryResource;
use App\Shared\Application\Gateway\GatewayException;

final readonly class GetCategoryProvider implements ProviderInterface
{
    public function __construct(
        private GetCategoryGateway $getCategoryGateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return null;
        }

        try {
            $request = GetCategoryRequest::fromData([
                'id' => $uriVariables['id'],
            ]);
            $response = ($this->getCategoryGateway)($request);

            $data = $response->data();

            return isset($data['category']) ? $this->transformToResource($data['category']) : null;
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null; // API Platform will return 404
            }

            throw $e;
        }
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
