<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

final readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
<?php if ($is_collection) { ?>
        // Collection query parameters
        public int $page = 1,
        public int $limit = 20,
        public ?string $sortBy = null,
        public ?string $sortOrder = 'asc',
        // TODO: Add filter parameters
        // public ?string $status = null,
        // public ?string $search = null,
<?php } else { ?>
        // Single item query parameters
        public string $id,
        // TODO: Add other query parameters if needed
<?php } ?>
    ) {
<?php if ($is_collection) { ?>
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }
        
        if ($this->limit < 1 || $this->limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
        
        if ($this->sortOrder !== null && !in_array($this->sortOrder, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException('Sort order must be "asc" or "desc"');
        }
<?php } ?>
    }
}
