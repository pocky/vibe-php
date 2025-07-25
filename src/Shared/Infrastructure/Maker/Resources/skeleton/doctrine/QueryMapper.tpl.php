<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Create<?php echo $entity_class_name; ?>\Model\<?php echo $entity_class_name; ?> as Create<?php echo $entity_class_name; ?>;
use App\<?php echo $context; ?>\Domain\Shared\Mapper\EntityToDomainMapper;
use App\<?php echo $context; ?>\Domain\Shared\ReadModel\<?php echo $entity_class_name; ?>ReadModel;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity_class_name; ?>Id;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\Timestamps;
use App\<?php echo $context; ?>\Infrastructure\Persistence\Doctrine\ORM\Entity\<?php echo $entity_class_name; ?> as Doctrine<?php echo $entity_class_name; ?>;

/**
 * Maps Doctrine entity to <?php echo $entity_class_name; ?> domain models for query operations.
 *
 * @implements EntityToDomainMapper<Doctrine<?php echo $entity_class_name; ?>, <?php echo $entity_class_name; ?>ReadModel>
 */
final class <?php echo $class_name; ?> implements EntityToDomainMapper
{
    public function map(mixed $entity): <?php echo $entity_class_name; ?>ReadModel
    {
        assert($entity instanceof Doctrine<?php echo $entity_class_name; ?>);

        return new <?php echo $entity_class_name; ?>ReadModel(
            id: new <?php echo $entity_class_name; ?>Id($entity->id->toRfc4122()),
            // TODO: Add other value objects based on entity properties
            timestamps: new Timestamps($entity->createdAt, $entity->updatedAt),
        );
    }

    /**
     * Maps Doctrine entity to Create<?php echo $entity_class_name; ?> domain model.
     */
    public function mapToCreateModel(Doctrine<?php echo $entity_class_name; ?> $entity): Create<?php echo $entity_class_name; ?>

    {
        $<?php echo strtolower((string) $entity_class_name); ?> = Create<?php echo $entity_class_name; ?>::create(
            id: new <?php echo $entity_class_name; ?>Id($entity->id->toRfc4122()),
            // TODO: Add other value objects based on entity properties
            createdAt: $entity->createdAt,
        );

        // Clear events since this is coming from persistence
        return $<?php echo strtolower((string) $entity_class_name); ?>->withEvents([]);
    }
}