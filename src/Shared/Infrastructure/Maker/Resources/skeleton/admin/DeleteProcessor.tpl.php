<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Gateway\Delete<?php echo $entity; ?>\Gateway as Delete<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Delete<?php echo $entity; ?>\Request as Delete<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\UI\Web\Admin\Resource\<?php echo $entity; ?>Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class <?php echo $class_name; ?> implements ProcessorInterface
{
    public function __construct(
        private Delete<?php echo $entity; ?>Gateway $delete<?php echo $entity; ?>Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var <?php echo $entity; ?>Resource $data */
        if (!$data instanceof <?php echo $entity; ?>Resource) {
            throw new \InvalidArgumentException('Expected <?php echo $entity; ?>Resource');
        }

        if (null === $data->id) {
            throw new \InvalidArgumentException('<?php echo $entity; ?> ID is required for deletion');
        }

        try {
            $gatewayRequest = Delete<?php echo $entity; ?>Request::fromData([
                '<?php echo $entity_camel; ?>Id' => $data->id,
            ]);

            ($this->delete<?php echo $entity; ?>Gateway)($gatewayRequest);

            // Return null for successful deletion
            return null;
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('<?php echo $entity; ?> not found', 404, $e);
            }
            throw $e;
        }
    }
}
