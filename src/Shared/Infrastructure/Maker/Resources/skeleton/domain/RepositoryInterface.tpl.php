<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $entity_class_name; ?>\Shared\Model\<?php echo $entity_class_name; ?>;
use App\<?php echo $context; ?>\Domain\<?php echo $entity_class_name; ?>\Shared\Specification\<?php echo $entity_class_name; ?>Specification;
use App\<?php echo $context; ?>\Domain\<?php echo $entity_class_name; ?>\Shared\ValueObject\<?php echo $entity_class_name; ?>Id;

interface <?php echo $class_name . "\n"; ?>
{
    public function save(<?php echo $entity_class_name; ?> $<?php echo $entity_variable; ?>): void;
    
    public function get(<?php echo $entity_class_name; ?>Id $id): <?php echo $entity_class_name; ?>;
    
    public function find(<?php echo $entity_class_name; ?>Id $id): ?<?php echo $entity_class_name; ?>;
    
    public function remove(<?php echo $entity_class_name; ?> $<?php echo $entity_variable; ?>): void;
    
    /**
     * @return <?php echo $entity_class_name; ?>[]
     */
    public function findAll(): array;
    
    /**
     * Find <?php echo strtolower((string) $entity_variable); ?>s matching the specification
     * 
     * @return <?php echo $entity_class_name; ?>[]
     */
    public function findSatisfying(<?php echo $entity_class_name; ?>Specification $specification): array;
    
    /**
     * Count <?php echo strtolower((string) $entity_variable); ?>s matching the specification
     */
    public function countSatisfying(<?php echo $entity_class_name; ?>Specification $specification): int;
}
