<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Event\<?php echo $entity; ?>Created;
use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Event\<?php echo $entity; ?>Updated;
use App\<?php echo $context; ?>\Domain\<?php echo $entity; ?>\Event\<?php echo $entity; ?>Deleted;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;
// TODO: Import other value objects

/**
 * <?php echo $entity; ?> Aggregate Root - Rich domain model with business behavior
 */
final class <?php echo $class_name . "\n"; ?>
{
    private array $events = [];
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private <?php echo $entity; ?>Id $id,
        // TODO: Add other value object properties
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public static function create(
        <?php echo $entity; ?>Id $id
        // TODO: Add other parameters
    ): self {
        $<?php echo lcfirst((string) $entity); ?> = new self(
            id: $id
            // TODO: Set other properties
        );

        $<?php echo lcfirst((string) $entity); ?>->recordEvent(new <?php echo $entity; ?>Created(
            <?php echo lcfirst((string) $entity); ?>Id: $id->getValue(),
            // TODO: Add event data
            createdAt: $<?php echo lcfirst((string) $entity); ?>->createdAt
        ));

        return $<?php echo lcfirst((string) $entity); ?>;
    }

    public function update(/* TODO: Add parameters */): void
    {
        if ($this->isDeleted()) {
            throw new \DomainException('Cannot update a deleted <?php echo strtolower((string) $entity); ?>');
        }

        $hasChanges = false;

        // TODO: Implement update logic
        // Example:
        // if (!$this->name->equals($name)) {
        //     $this->name = $name;
        //     $hasChanges = true;
        // }

        if ($hasChanges) {
            $this->updatedAt = new \DateTimeImmutable();
            $this->recordEvent(new <?php echo $entity; ?>Updated(
                <?php echo lcfirst((string) $entity); ?>Id: $this->id->getValue(),
                // TODO: Add event data
                updatedAt: $this->updatedAt
            ));
        }
    }

    public function delete(): void
    {
        if ($this->isDeleted()) {
            return; // Idempotent operation
        }

        // TODO: Add any business rules for deletion

        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new <?php echo $entity; ?>Deleted(
            <?php echo lcfirst((string) $entity); ?>Id: $this->id->getValue(),
            deletedAt: $this->updatedAt
        ));
    }

    // Getters
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

    public function isDeleted(): bool
    {
        // TODO: Implement deletion check based on your business logic
        return false;
    }

    // Event handling
    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
