<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;

/**
 * Represents <?php echo strtolower((string) $entity); ?> data during <?php echo strtolower(str_replace($entity, '', $use_case)); ?>.
 * This is a data transfer object specific to the <?php echo $use_case; ?> operation.
 */
final readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        public <?php echo $entity; ?>Id $id,
        // TODO: Add other value objects as properties
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        private array $events = []
    ) {
    }

    public static function create(
        <?php echo $entity; ?>Id $id,
        // TODO: Add other parameters
    ): self {
        $now = new \DateTimeImmutable();

        return new self(
            id: $id,
            // TODO: Set other properties
            createdAt: $now,
            updatedAt: $now,
            events: [],
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            // TODO: Copy other properties
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function releaseEvents(): array
    {
        return $this->events;
    }

    public function id(): <?php echo $entity; ?>Id
    {
        return $this->id;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}