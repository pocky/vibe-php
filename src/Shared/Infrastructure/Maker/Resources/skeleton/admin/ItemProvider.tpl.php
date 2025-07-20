<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Gateway\Get<?php echo $entity; ?>\Gateway as Get<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Get<?php echo $entity; ?>\Request as Get<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\UI\Web\Admin\Resource\<?php echo $entity; ?>Resource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class <?php echo $class_name; ?> implements ProviderInterface
{
    public function __construct(
        private Get<?php echo $entity; ?>Gateway $get<?php echo $entity; ?>Gateway,
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
            $gatewayRequest = Get<?php echo $entity; ?>Request::fromData(['id' => $id]);
            $gatewayResponse = ($this->get<?php echo $entity; ?>Gateway)($gatewayRequest);
            
            $data = $gatewayResponse->data();
            return isset($data['<?php echo $entity_camel; ?>']) ? $this->transformToResource($data['<?php echo $entity_camel; ?>']) : null;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null;
            }
            throw $e;
        }
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
