<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\DataPersister\<?php echo $entity; ?>;
use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\Exception\<?php echo $entity; ?>AlreadyExists;
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
        \DateTimeImmutable $createdAt,
    ): <?php echo $entity . "\n"; ?>
    {
        // TODO: Add business logic validation
        // Example: Check if entity already exists
        // if ($this->repository->existsById($<?php echo $entity_snake; ?>Id)) {
        //     throw new <?php echo $entity; ?>AlreadyExists($<?php echo $entity_snake; ?>Id);
        // }

        // Create the aggregate
        $<?php echo $entity_snake; ?> = new <?php echo $entity; ?>(
            id: $<?php echo $entity_snake; ?>Id,
            // TODO: Pass other value objects
            createdAt: $createdAt,
        );

        // Persist
        $this->repository->save($<?php echo $entity_snake; ?>);

        // Return aggregate with unreleased events for Application layer to handle
        return $<?php echo $entity_snake; ?>;
    }
}