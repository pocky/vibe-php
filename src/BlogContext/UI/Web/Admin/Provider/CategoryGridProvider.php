<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Provider;

use App\BlogContext\Application\Gateway\ListCategories\Gateway as ListCategoriesGateway;
use App\BlogContext\Application\Gateway\ListCategories\Request as ListCategoriesRequest;
use App\BlogContext\UI\Web\Admin\Resource\CategoryResource;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class CategoryGridProvider implements DataProviderInterface
{
    public function __construct(
        private ListCategoriesGateway $listCategoriesGateway,
    ) {
    }

    public function getData(Grid $grid, Parameters $parameters): Pagerfanta
    {
        // Get current page and items per page from grid parameters
        $page = max(1, (int) $parameters->get('page', 1));
        $itemsPerPage = max(1, (int) $parameters->get('limit', 10));

        // Get criteria from parameters (for filtering)
        $criteria = $parameters->get('criteria', []);

        // Create gateway request
        $gatewayRequest = ListCategoriesRequest::fromData([
            'page' => $page,
            'limit' => $itemsPerPage,
            // TODO: Add any filter criteria here if needed
        ]);

        // Execute gateway
        $gatewayResponse = ($this->listCategoriesGateway)($gatewayRequest);
        $responseData = $gatewayResponse->data();

        // Transform response to CategoryResource objects
        /** @var array<CategoryResource> $categories */
        $categories = [];

        if (isset($responseData['categories']) && is_array($responseData['categories'])) {
            foreach ($responseData['categories'] as $categoryData) {
                if (is_array($categoryData)) {
                    $categories[] = $this->transformToResource($categoryData);
                }
            }
        }

        // Get total count from response
        $totalCount = $responseData['total'] ?? count($categories);

        // Create a FixedAdapter with the pre-paginated data
        $adapter = new FixedAdapter($totalCount, $categories);
        $pagerfanta = new Pagerfanta($adapter);

        // Set current page and max per page
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($itemsPerPage);

        return $pagerfanta;
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
