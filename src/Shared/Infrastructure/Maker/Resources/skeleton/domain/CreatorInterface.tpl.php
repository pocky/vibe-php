<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\DataPersister\<?php echo $entity; ?>;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;

interface <?php echo $class_name . "\n"; ?>
{
    public function __invoke(
        <?php echo $entity; ?>Id $<?php echo lcfirst((string) $entity); ?>Id,
        // TODO: Add other value objects as parameters
        \DateTimeImmutable $createdAt,
    ): <?php echo $entity; ?>;
}