<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity_class_name; ?>Id;

interface <?php echo $class_name . "\n"; ?>
{
    public function save(object $<?php echo $entity_variable; ?>): void;

    public function findById(<?php echo $entity_class_name; ?>Id $id): object|null;

    public function remove(object $<?php echo $entity_variable; ?>): void;
}