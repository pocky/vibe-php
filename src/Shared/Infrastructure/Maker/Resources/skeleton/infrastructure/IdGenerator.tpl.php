<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $id_class_full_name; ?>;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class <?php echo $class_name; ?>
{
    public function __construct(
        private GeneratorInterface $generator,
    ) {
    }

    public function nextIdentity(): <?php echo $id_class_short_name; ?>
    {
        return new <?php echo $id_class_short_name; ?>($this->generator::generate());
    }
}