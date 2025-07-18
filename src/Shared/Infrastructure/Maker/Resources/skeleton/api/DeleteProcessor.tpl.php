<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\<?php echo $context; ?>\Application\Gateway\Delete<?php echo $entity; ?>\Gateway as Delete<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Delete<?php echo $entity; ?>\Request as Delete<?php echo $entity; ?>Request;

final readonly class <?php echo $class_name; ?> implements ProcessorInterface
{
    public function __construct(
        private Delete<?php echo $entity; ?>Gateway $delete<?php echo $entity; ?>Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!isset($uriVariables['id'])) {
            throw new \InvalidArgumentException('<?php echo $entity; ?> ID is required for deletion');
        }

        try {
            $request = Delete<?php echo $entity; ?>Request::fromData([
                '<?php echo $entity_camel; ?>Id' => $uriVariables['id'],
            ]);

            ($this->delete<?php echo $entity; ?>Gateway)($request);

            // Return null for successful deletion (204 No Content)
            return null;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('<?php echo $entity; ?> not found', 404, $e);
            }
            throw $e;
        }
    }
}