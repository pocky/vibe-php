<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

interface <?php echo $class_name . "\n"; ?>
{
    public function __invoke(Query $query): View;
}