<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\<?php echo $use_case; ?>\Event\<?php echo $event_name; ?>;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;

final class <?php echo $class_name . "\n"; ?>
{
    private array $domainEvents = [];

    public function __construct(
        public <?php echo $entity; ?>Id $id {
            get => $this->id;
        },
        // TODO: Add other value objects as properties
        public \DateTimeImmutable $createdAt {
            get => $this->createdAt;
        },
    ) {
        // Emit domain event on creation
        $this->domainEvents[] = new <?php echo $event_name; ?>(
            <?php echo $entity_snake; ?>Id: $this->id,
            createdAt: $this->createdAt
        );
    }

    // TODO: Add business methods here
    // Example:
    // public function update(Title $title): void
    // {
    //     $this->title = $title;
    //     $this->updatedAt = new \DateTimeImmutable();
    //     $this->domainEvents[] = new <?php echo $entity; ?>Updated($this->id, $title);
    // }

    // Domain event management
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function hasUnreleasedEvents(): bool
    {
        return [] !== $this->domainEvents;
    }
}