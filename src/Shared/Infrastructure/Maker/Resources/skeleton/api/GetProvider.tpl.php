<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\<?php echo $context; ?>\Application\Gateway\Get<?php echo $entity; ?>\Gateway as Get<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Get<?php echo $entity; ?>\Request as Get<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\UI\Api\Rest\Resource\<?php echo $entity; ?>Resource;
use App\Shared\Application\Gateway\GatewayException;

final readonly class <?php echo $class_name; ?> implements ProviderInterface
{
    public function __construct(
        private Get<?php echo $entity; ?>Gateway $get<?php echo $entity; ?>Gateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return null;
        }

        try {
            $request = Get<?php echo $entity; ?>Request::fromData(['id' => $uriVariables['id']]);
            $response = ($this->get<?php echo $entity; ?>Gateway)($request);
            
            $data = $response->data();
            return isset($data['<?php echo $entity_camel; ?>']) ? $this->transformToResource($data['<?php echo $entity_camel; ?>']) : null;
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null; // API Platform will return 404
            }
            throw $e;
        }
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