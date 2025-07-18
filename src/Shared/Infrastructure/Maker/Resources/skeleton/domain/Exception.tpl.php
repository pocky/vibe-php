<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;

final class <?php echo $class_name; ?> extends \DomainException
{
    public function __construct(<?php echo $entity; ?>Id $<?php echo $entity_snake; ?>Id)
    {
        parent::__construct(
            sprintf(
                '<?php echo $entity; ?> with ID "%s" already exists.',
                $<?php echo $entity_snake; ?>Id->getValue()
            )
        );
    }
}