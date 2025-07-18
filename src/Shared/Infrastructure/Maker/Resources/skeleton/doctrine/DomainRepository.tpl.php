<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Shared\Repository\<?php echo $entity_class_name; ?>RepositoryInterface;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity_class_name; ?>Id;
use <?php echo $entity_full_class_name; ?>;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class <?php echo $class_name; ?> implements <?php echo $entity_class_name; ?>RepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(object $<?php echo $entity_variable; ?>): void
    {
        // Get the <?php echo $entity_variable; ?> ID to find existing entity
        $<?php echo $entity_variable; ?>Id = $this->extract<?php echo $entity_class_name; ?>Id($<?php echo $entity_variable; ?>);
        $existingEntity = null;

        if ($<?php echo $entity_variable; ?>Id instanceof <?php echo $entity_class_name; ?>Id) {
            $existingEntity = $this->entityManager->find(
                <?php echo $entity_class_name; ?>::class,
                Uuid::fromString($<?php echo $entity_variable; ?>Id->getValue())
            );
        }

        // For now, we'll need a mapper when we have domain models
        // This is a simplified version
        if (!$existingEntity instanceof <?php echo $entity_class_name; ?>) {
            $entity = new <?php echo $entity_class_name; ?>();
            $this->entityManager->persist($entity);
        } else {
            $entity = $existingEntity;
        }

        // Update entity properties from domain model
        // TODO: Implement mapper to convert domain model to entity

        $this->entityManager->flush();
    }

    /**
     * Extract <?php echo $entity_class_name; ?>Id from various domain <?php echo $entity_variable; ?> types
     */
    private function extract<?php echo $entity_class_name; ?>Id(object $<?php echo $entity_variable; ?>): <?php echo $entity_class_name; ?>Id|null
    {
        // Check common property names
        if (property_exists($<?php echo $entity_variable; ?>, 'id') && $<?php echo $entity_variable; ?>->id instanceof <?php echo $entity_class_name; ?>Id) {
            return $<?php echo $entity_variable; ?>->id;
        }

        // Check for getter methods
        if (method_exists($<?php echo $entity_variable; ?>, 'get<?php echo $entity_class_name; ?>Id')) {
            /** @var <?php echo $entity_class_name; ?>Id */
            return $<?php echo $entity_variable; ?>->get<?php echo $entity_class_name; ?>Id();
        }

        if (method_exists($<?php echo $entity_variable; ?>, 'getId')) {
            /** @var <?php echo $entity_class_name; ?>Id */
            return $<?php echo $entity_variable; ?>->getId();
        }

        return null;
    }

    public function findById(<?php echo $entity_class_name; ?>Id $id): object|null
    {
        $entity = $this->entityManager->find(<?php echo $entity_class_name; ?>::class, Uuid::fromString($id->toString()));

        if (null === $entity) {
            return null;
        }

        // TODO: Use a mapper to convert entity to domain model
        // For now, returning the entity
        return $entity;
    }

    public function remove(object $<?php echo $entity_variable; ?>): void
    {
        $<?php echo $entity_variable; ?>Id = $this->extract<?php echo $entity_class_name; ?>Id($<?php echo $entity_variable; ?>);

        if ($<?php echo $entity_variable; ?>Id instanceof <?php echo $entity_class_name; ?>Id) {
            $entity = $this->entityManager->find(<?php echo $entity_class_name; ?>::class, Uuid::fromString($<?php echo $entity_variable; ?>Id->getValue()));
            if ($entity instanceof <?php echo $entity_class_name; ?>) {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
            }
        }
    }
}