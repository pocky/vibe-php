<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php if ($is_collection) { ?>
final readonly class <?php echo $class_name . "\n"; ?>
{
    /**
     * @param array<ItemView> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(fn (ItemView $item) => $item->toArray(), $this->items),
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}

final readonly class ItemView
{
    public function __construct(
        public string $id,
        // TODO: Add other fields
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            // TODO: Add other fields
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
<?php } else { ?>
final readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        public string $id,
        // TODO: Add other fields based on your domain
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            // TODO: Add other fields
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
<?php } ?>