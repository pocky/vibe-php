<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

final readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        // TODO: Add command properties
        // Example:
        // public string $<?php echo $entity_snake; ?>Id,
        // public string $title,
        // public string $content,
        // public string $status = 'draft',
    ) {
        // TODO: Add validation if needed
        // Example:
        // if ('' === $this->title) {
        //     throw new \InvalidArgumentException('Title cannot be empty');
        // }
    }
}