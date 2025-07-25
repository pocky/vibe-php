<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Operation\Command\<?php echo $command_name; ?>\Command;
use App\<?php echo $context; ?>\Application\Operation\Command\<?php echo $command_name; ?>\HandlerInterface;
use App\<?php echo $context; ?>\Domain\<?php echo $command_name; ?>\CreatorInterface;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class <?php echo $class_name; ?> implements HandlerInterface
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Convert string values to domain value objects
        $<?php echo $entity_snake; ?>Id = new <?php echo $entity; ?>Id($command-><?php echo $entity_snake; ?>Id);
        // TODO: Add other value object conversions
        // Example:
        // $title = new Title($command->title);
        // $content = new Content($command->content);

        // Call domain creator to get model with domain events
        $<?php echo $entity_snake; ?> = ($this->creator)(
            <?php echo $entity_snake; ?>Id: $<?php echo $entity_snake; ?>Id,
            // TODO: Pass other value objects
        );

        // Dispatch domain events via EventBus (if events exist)
        foreach ($<?php echo $entity_snake; ?>->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
