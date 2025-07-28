<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Provider;

use App\Blog\Application\Gateway\Tag\ListTags\Gateway as ListTagsGateway;
use App\Blog\Application\Gateway\Tag\ListTags\Request as ListTagsRequest;
use App\Blog\UI\Web\Admin\Resource\TagResource;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class TagGridProvider implements DataProviderInterface
{
    public function __construct(
        private ListTagsGateway $listTagsGateway,
    ) {
    }

    public function getData(Grid $grid, Parameters $parameters): Pagerfanta
    {
        // Get current page and items per page from grid parameters
        $page = max(1, (int) $parameters->get('page', 1));
        $itemsPerPage = max(1, (int) $parameters->get('limit', 10));

        // Get criteria from parameters (for filtering)
        /** @var array<string, mixed> $criteria */
        $criteria = $parameters->get('criteria', []);
        /** @var array<string, mixed> $sorting */
        $sorting = $parameters->get('sorting', []);

        // Build request data with filters and sorting
        $requestData = [
            'page' => $page,
            'limit' => $itemsPerPage,
        ];

        // Add filters
        if (!empty($criteria)) {
            if (isset($criteria['name']) && is_string($criteria['name'])) {
                $requestData['name'] = $criteria['name'];
            }

            if (isset($criteria['slug']) && is_string($criteria['slug'])) {
                $requestData['slug'] = $criteria['slug'];
            }

            if (isset($criteria['createdAt']) && is_array($criteria['createdAt'])) {
                /** @var array<string, mixed> $createdAtCriteria */
                $createdAtCriteria = $criteria['createdAt'];
                if (isset($createdAtCriteria['from']) && is_string($createdAtCriteria['from'])) {
                    $requestData['createdAtFrom'] = $createdAtCriteria['from'];
                }

                if (isset($createdAtCriteria['to']) && is_string($createdAtCriteria['to'])) {
                    $requestData['createdAtTo'] = $createdAtCriteria['to'];
                }
            }
        }

        // Add sorting
        if (!empty($sorting)) {
            $sortField = array_key_first($sorting);
            if (null !== $sortField && isset($sorting[$sortField])) {
                $sortDirection = $sorting[$sortField];
                $requestData['sortBy'] = $sortField;
                $requestData['sortDirection'] = $sortDirection;
            }
        }

        // Create gateway request
        $gatewayRequest = ListTagsRequest::fromData($requestData);

        // Execute gateway
        $gatewayResponse = ($this->listTagsGateway)($gatewayRequest);
        $responseData = $gatewayResponse->data();

        // Transform response to TagResource objects
        /** @var array<TagResource> $tags */
        $tags = [];

        if (isset($responseData['tags']) && is_array($responseData['tags'])) {
            /** @var mixed $tagData */
            foreach ($responseData['tags'] as $tagData) {
                if (is_array($tagData)) {
                    $tags[] = $this->transformToResource($tagData);
                }
            }
        }

        // Get total count from response
        $totalCount = (int) ($responseData['total'] ?? count($tags));

        // Create a FixedAdapter with the pre-paginated data
        $fixedAdapter = new FixedAdapter($totalCount, $tags);
        $pagerfanta = new Pagerfanta($fixedAdapter);

        // Set current page and max per page
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($itemsPerPage);

        return $pagerfanta;
    }

    private function transformToResource(array $data): TagResource
    {
        return new TagResource(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            articleCount: $data['articleCount'] ?? 0,
            createdAt: isset($data['createdAt']) && $data['createdAt']
                ? new \DateTimeImmutable($data['createdAt'])
                : null,
            updatedAt: isset($data['updatedAt']) && $data['updatedAt']
                ? new \DateTimeImmutable($data['updatedAt'])
                : null,
        );
    }
}
