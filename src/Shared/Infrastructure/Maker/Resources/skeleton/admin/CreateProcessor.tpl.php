<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Gateway\Create<?php echo $entity; ?>\Gateway as Create<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Create<?php echo $entity; ?>\Request as Create<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\Domain\Create<?php echo $entity; ?>\Exception\<?php echo $entity; ?>AlreadyExists;
use App\<?php echo $context; ?>\UI\Web\Admin\Resource\<?php echo $entity; ?>Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class <?php echo $class_name; ?> implements ProcessorInterface
{
    public function __construct(
        private Create<?php echo $entity; ?>Gateway $create<?php echo $entity; ?>Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var <?php echo $entity; ?>Resource $data */
        if (!$data instanceof <?php echo $entity; ?>Resource) {
            throw new \InvalidArgumentException('Expected <?php echo $entity; ?>Resource');
        }

        try {
            $gatewayRequest = Create<?php echo $entity; ?>Request::fromData([
                // TODO: Map resource data to gateway request
                // 'name' => $data->name,
                // 'description' => $data->description,
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $gatewayResponse = ($this->create<?php echo $entity; ?>Gateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource with generated data
            return new <?php echo $entity; ?>Resource(
                id: $responseData['<?php echo $entity_camel; ?>Id'],
                // TODO: Map response data to resource
                // name: $data->name,
                // description: $data->description,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (<?php echo $entity; ?>AlreadyExists $e) {
            throw new \RuntimeException('<?php echo $entity; ?> already exists', 409, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new \RuntimeException('<?php echo $entity; ?> already exists', 409, $e);
            }
            throw $e;
        }
    }
}
