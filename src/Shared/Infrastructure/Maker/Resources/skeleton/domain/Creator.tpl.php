<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Shared\Model\<?php echo $entity; ?>;
use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Shared\Repository\<?php echo $entity; ?>WriteRepositoryInterface;
use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Shared\Exception\<?php echo $entity; ?>AlreadyExists;
use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Shared\Identifier\<?php echo $entity; ?>Id;
// TODO: Import other value objects

final readonly class <?php echo $class_name; ?>
{
    public function __construct(
        private <?php echo $entity; ?>WriteRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        <?php echo $entity; ?>Id $<?php echo $entity_snake; ?>Id,
        // TODO: Add other value objects as parameters
    ): <?php echo $entity . "\n"; ?>
    {
        // TODO: Add business logic validation
        // Example: Check if entity already exists
        // if ($this->repository->find($<?php echo $entity_snake; ?>Id) !== null) {
        //     throw new <?php echo $entity; ?>AlreadyExists($<?php echo $entity_snake; ?>Id);
        // }

        // Create the <?php echo strtolower((string) $entity); ?> using the rich domain model
        $<?php echo $entity_snake; ?> = <?php echo $entity; ?>::create(
            id: $<?php echo $entity_snake; ?>Id,
            // TODO: Pass other value objects
        );

        // Persist the <?php echo strtolower((string) $entity); ?>
        $this->repository->save($<?php echo $entity_snake; ?>);

        // Return the <?php echo strtolower((string) $entity); ?> with its events
        return $<?php echo $entity_snake; ?>;
    }
}
