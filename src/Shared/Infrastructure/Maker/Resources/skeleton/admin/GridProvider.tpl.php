<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Gateway\List<?php echo $entity_plural_pascal; ?>\Gateway as List<?php echo $entity_plural_pascal; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\List<?php echo $entity_plural_pascal; ?>\Request as List<?php echo $entity_plural_pascal; ?>Request;
use App\<?php echo $context; ?>\UI\Web\Admin\Resource\<?php echo $entity; ?>Resource;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class <?php echo $class_name; ?> implements DataProviderInterface
{
    public function __construct(
        private List<?php echo $entity_plural_pascal; ?>Gateway $list<?php echo $entity_plural_pascal; ?>Gateway,
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
        $gatewayRequest = List<?php echo $entity_plural_pascal; ?>Request::fromData([
            'page' => $page,
            'limit' => $itemsPerPage,
            // TODO: Add any filter criteria here if needed
        ]);

        // Execute gateway
        $gatewayResponse = ($this->list<?php echo $entity_plural_pascal; ?>Gateway)($gatewayRequest);
        $responseData = $gatewayResponse->data();

        // Transform response to <?php echo $entity; ?>Resource objects
        /** @var array<<?php echo $entity; ?>Resource> $<?php echo $entity_plural_camel; ?> */
        $<?php echo $entity_plural_camel; ?> = [];
        if (isset($responseData['<?php echo $entity_plural_camel; ?>']) && is_array($responseData['<?php echo $entity_plural_camel; ?>'])) {
            foreach ($responseData['<?php echo $entity_plural_camel; ?>'] as $<?php echo $entity_camel; ?>Data) {
                if (is_array($<?php echo $entity_camel; ?>Data)) {
                    $<?php echo $entity_plural_camel; ?>[] = $this->transformToResource($<?php echo $entity_camel; ?>Data);
                }
            }
        }

        // Get total count from response
        $totalCount = $responseData['total'] ?? count($<?php echo $entity_plural_camel; ?>);

        // Create a FixedAdapter with the pre-paginated data
        $adapter = new FixedAdapter($totalCount, $<?php echo $entity_plural_camel; ?>);
        $pagerfanta = new Pagerfanta($adapter);

        // Set current page and max per page
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($itemsPerPage);

        return $pagerfanta;
    }

    private function transformToResource(array $data): <?php echo $entity; ?>Resource
    {
        return new <?php echo $entity; ?>Resource(
            id: $data['id'] ?? null,
            // TODO: Map other properties
            // name: $data['name'] ?? null,
            // description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) && $data['created_at']
                ? new \DateTimeImmutable($data['created_at'])
                : null,
            updatedAt: isset($data['updated_at']) && $data['updated_at']
                ? new \DateTimeImmutable($data['updated_at'])
                : null,
        );
    }
}
