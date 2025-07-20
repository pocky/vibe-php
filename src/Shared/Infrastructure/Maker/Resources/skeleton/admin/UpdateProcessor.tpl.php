<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Gateway\Update<?php echo $entity; ?>\Gateway as Update<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Update<?php echo $entity; ?>\Request as Update<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\UI\Web\Admin\Resource\<?php echo $entity; ?>Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class <?php echo $class_name; ?> implements ProcessorInterface
{
    public function __construct(
        private Update<?php echo $entity; ?>Gateway $update<?php echo $entity; ?>Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var <?php echo $entity; ?>Resource $data */
        if (!$data instanceof <?php echo $entity; ?>Resource) {
            throw new \InvalidArgumentException('Expected <?php echo $entity; ?>Resource');
        }

        if (null === $data->id) {
            throw new \InvalidArgumentException('<?php echo $entity; ?> ID is required for update');
        }

        try {
            $gatewayRequest = Update<?php echo $entity; ?>Request::fromData([
                '<?php echo $entity_camel; ?>Id' => $data->id,
                // TODO: Map resource data to gateway request
                // 'name' => $data->name,
                // 'description' => $data->description,
            ]);

            $gatewayResponse = ($this->update<?php echo $entity; ?>Gateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource
            return new <?php echo $entity; ?>Resource(
                id: $data->id,
                // TODO: Map response data to resource
                // name: $responseData['name'] ?? $data->name,
                // description: $responseData['description'] ?? $data->description,
                createdAt: $data->createdAt,
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('<?php echo $entity; ?> not found', 404, $e);
            }
            throw $e;
        }
    }
}
