<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\<?php echo $context; ?>\Application\Gateway\List<?php echo $entity_plural_pascal; ?>\Gateway as List<?php echo $entity_plural_pascal; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\List<?php echo $entity_plural_pascal; ?>\Request as List<?php echo $entity_plural_pascal; ?>Request;
use App\<?php echo $context; ?>\UI\Api\Rest\Resource\<?php echo $entity; ?>Resource;

final readonly class <?php echo $class_name; ?> implements ProviderInterface
{
    public function __construct(
        private List<?php echo $entity_plural_pascal; ?>Gateway $list<?php echo $entity_plural_pascal; ?>Gateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filters = $context['filters'] ?? [];
        
        $request = List<?php echo $entity_plural_pascal; ?>Request::fromData([
            'page' => (int) ($filters['page'] ?? 1),
            'limit' => (int) ($filters['itemsPerPage'] ?? 20),
            // TODO: Add filter parameters
            // 'status' => $filters['status'] ?? null,
            // 'search' => $filters['search'] ?? null,
        ]);
        
        $response = ($this->list<?php echo $entity_plural_pascal; ?>Gateway)($request);
        
        $data = $response->data();
        $<?php echo $entity_plural; ?> = $data['<?php echo $entity_plural; ?>'] ?? [];
        
        return array_map(
            fn (array $<?php echo $entity_camel; ?>) => $this->transformToResource($<?php echo $entity_camel; ?>),
            $<?php echo $entity_plural; ?>
        );
    }
    
    private function transformToResource(array $data): <?php echo $entity; ?>Resource
    {
        return new <?php echo $entity; ?>Resource(
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
