<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Provider;

use App\BlogContext\Application\Gateway\GetCategory\Gateway as GetCategoryGateway;
use App\BlogContext\Application\Gateway\GetCategory\Request as GetCategoryRequest;
use App\BlogContext\UI\Web\Admin\Resource\CategoryResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class CategoryItemProvider implements ProviderInterface
{
    public function __construct(
        private GetCategoryGateway $getCategoryGateway,
    ) {
    }

    public function provide(Operation $operation, Context $context): object|array|null
    {
        /** @var RequestOption|null $requestOption */
        $requestOption = $context->get(RequestOption::class);
        $request = $requestOption?->request();
        $id = $request?->attributes->get('id');

        if (null === $id) {
            return null;
        }

        try {
            $gatewayRequest = GetCategoryRequest::fromData([
                'id' => $id,
            ]);
            $gatewayResponse = ($this->getCategoryGateway)($gatewayRequest);

            $data = $gatewayResponse->data();

            return isset($data['category']) ? $this->transformToResource($data['category']) : null;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null;
            }

            throw $e;
        }
    }

    private function transformToResource(array $data): CategoryResource
    {
        return new CategoryResource(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            path: $data['path'] ?? null,
            parentId: $data['parentId'] ?? null,
            level: (int) ($data['level'] ?? 1),
            articleCount: (int) ($data['articleCount'] ?? 0),
            description: $data['description'] ?? null,
            createdAt: isset($data['createdAt']) && $data['createdAt']
                ? new \DateTimeImmutable($data['createdAt'])
                : null,
            updatedAt: isset($data['updatedAt']) && $data['updatedAt']
                ? new \DateTimeImmutable($data['updatedAt'])
                : null,
        );
    }
}
