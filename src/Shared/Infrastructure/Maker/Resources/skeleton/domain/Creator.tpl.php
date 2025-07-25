<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\Event\<?php echo $entity; ?>Created;
use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\Exception\<?php echo $entity; ?>AlreadyExists;
use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\Model\<?php echo $entity; ?>;
use App\<?php echo $context; ?>\Domain\Shared\Repository\<?php echo $entity; ?>RepositoryInterface;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;

final readonly class <?php echo $class_name; ?> implements CreatorInterface
{
    public function __construct(
        private <?php echo $entity; ?>RepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        <?php echo $entity; ?>Id $<?php echo $entity_snake; ?>Id,
        // TODO: Add other value objects as parameters
    ): <?php echo $entity . "\n"; ?>
    {
        // TODO: Add business logic validation
        // Example: Check if entity already exists
        // if ($this->repository->existsById($<?php echo $entity_snake; ?>Id)) {
        //     throw new <?php echo $entity; ?>AlreadyExists($<?php echo $entity_snake; ?>Id);
        // }

        // Create domain model
        $<?php echo $entity_snake; ?> = <?php echo $entity; ?>::create(
            id: $<?php echo $entity_snake; ?>Id,
            // TODO: Pass other value objects
        );

        // Create domain event
        $event = new <?php echo $entity; ?>Created(
            <?php echo $entity_snake; ?>Id: $<?php echo $entity_snake; ?>Id->getValue(),
            createdAt: $<?php echo $entity_snake; ?>->createdAt,
        );

        // Attach event to model
        $<?php echo $entity_snake; ?> = $<?php echo $entity_snake; ?>->withEvents([$event]);

        // Persist
        $this->repository->add($<?php echo $entity_snake; ?>);

        // Return model with events
        return $<?php echo $entity_snake; ?>;
    }
}
