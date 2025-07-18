# Admin Provider Templates

## Grid Provider Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Provider;

use App\[Context]Context\Application\Gateway\List[Resources]\Gateway as List[Resources]Gateway;
use App\[Context]Context\Application\Gateway\List[Resources]\Request as List[Resources]Request;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class [Resource]GridProvider implements DataProviderInterface
{
    public function __construct(
        private List[Resources]Gateway $list[Resources]Gateway,
    ) {
    }

    public function getData(Grid $grid, Parameters $parameters): Pagerfanta
    {
        // Get current page and items per page from grid parameters
        $page = max(1, (int) $parameters->get('page', 1));
        $itemsPerPage = max(1, (int) $parameters->get('limit', 10));

        // Get criteria from parameters (for filtering)
        $criteria = $parameters->get('criteria', []);

        // Create gateway request with filters
        $gatewayRequest = List[Resources]Request::fromData([
            'page' => $page,
            'limit' => $itemsPerPage,
            'name' => $criteria['name'] ?? null,
            'status' => $criteria['status'] ?? null,
            'search' => $criteria['search'] ?? null,
        ]);

        // Execute gateway
        $gatewayResponse = ($this->list[Resources]Gateway)($gatewayRequest);
        $responseData = $gatewayResponse->data();

        // Transform response to Resource objects
        $[resources] = [];
        if (isset($responseData['[resources]']) && is_array($responseData['[resources]'])) {
            foreach ($responseData['[resources]'] as $[resource]Data) {
                if (is_array($[resource]Data)) {
                    $[resources][] = $this->transformToResource($[resource]Data);
                }
            }
        }

        // Get total count from response
        $totalCount = $responseData['total'] ?? count($[resources]);

        // Create a FixedAdapter with the pre-paginated data
        $adapter = new FixedAdapter($totalCount, $[resources]);
        $pagerfanta = new Pagerfanta($adapter);

        // Set current page and max per page
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($itemsPerPage);

        return $pagerfanta;
    }

    private function transformToResource(array $data): [Resource]Resource
    {
        return new [Resource]Resource(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            slug: $data['slug'] ?? null,
            status: $data['status'] ?? null,
            createdAt: isset($data['created_at']) && $data['created_at']
                ? new \DateTimeImmutable($data['created_at'])
                : null,
            updatedAt: isset($data['updated_at']) && $data['updated_at']
                ? new \DateTimeImmutable($data['updated_at'])
                : null,
        );
    }
}
```

## Item Provider Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Provider;

use App\[Context]Context\Application\Gateway\Get[Resource]\Gateway as Get[Resource]Gateway;
use App\[Context]Context\Application\Gateway\Get[Resource]\Request as Get[Resource]Request;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Component\Resource\Context\Context;
use Sylius\Component\Resource\Metadata\Operation;
use Sylius\Component\Resource\State\ProviderInterface;

final readonly class [Resource]ItemProvider implements ProviderInterface
{
    public function __construct(
        private Get[Resource]Gateway $get[Resource]Gateway,
    ) {
    }

    public function provide(Operation $operation, array|object $context): ?object
    {
        if ($context instanceof Context) {
            $httpRequestContext = $context->get('http_request');
            $[resource]Id = $httpRequestContext['id'] ?? null;
        } else {
            $[resource]Id = $context['id'] ?? null;
        }

        if (null === $[resource]Id) {
            return null;
        }

        try {
            $request = Get[Resource]Request::fromData(['id' => $[resource]Id]);
            $response = ($this->get[Resource]Gateway)($request);
            $responseData = $response->data();
            
            return isset($responseData['[resource]']) 
                ? $this->transformToResource($responseData['[resource]']) 
                : null;
        } catch (GatewayException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null;
            }
            throw $e;
        }
    }

    private function transformToResource(array $data): [Resource]Resource
    {
        return new [Resource]Resource(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            slug: $data['slug'] ?? null,
            status: $data['status'] ?? null,
            createdAt: isset($data['created_at']) && $data['created_at']
                ? new \DateTimeImmutable($data['created_at'])
                : null,
            updatedAt: isset($data['updated_at']) && $data['updated_at']
                ? new \DateTimeImmutable($data['updated_at'])
                : null,
        );
    }
}
```

## Provider with Filters

```php
// Advanced Grid Provider with filtering support
public function getData(Grid $grid, Parameters $parameters): Pagerfanta
{
    $page = max(1, (int) $parameters->get('page', 1));
    $itemsPerPage = max(1, (int) $parameters->get('limit', 10));
    
    // Extract filters
    $criteria = $parameters->get('criteria', []);
    $sorting = $parameters->get('sorting', []);
    
    // Build gateway request with all filters
    $requestData = [
        'page' => $page,
        'limit' => $itemsPerPage,
    ];
    
    // Add search filter
    if (!empty($criteria['search']['value'])) {
        $requestData['search'] = $criteria['search']['value'];
    }
    
    // Add status filter
    if (!empty($criteria['status']['value'])) {
        $requestData['status'] = $criteria['status']['value'];
    }
    
    // Add date range filter
    if (!empty($criteria['createdAt']['from'])) {
        $requestData['createdFrom'] = $criteria['createdAt']['from'];
    }
    if (!empty($criteria['createdAt']['to'])) {
        $requestData['createdTo'] = $criteria['createdAt']['to'];
    }
    
    // Add sorting
    if (!empty($sorting)) {
        $field = key($sorting);
        $direction = current($sorting);
        $requestData['orderBy'] = $field;
        $requestData['orderDirection'] = $direction;
    }
    
    $gatewayRequest = List[Resources]Request::fromData($requestData);
    
    // Continue with gateway execution...
}
```