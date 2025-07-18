<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Operation\Command\<?php echo $command_name; ?>\Command;
use App\<?php echo $context; ?>\Domain\Shared\Repository\<?php echo $entity; ?>RepositoryInterface;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use App\Shared\Infrastructure\Generator\GeneratorInterface;

final readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        private <?php echo $entity; ?>RepositoryInterface $repository,
        private EventBusInterface $eventBus,
        private GeneratorInterface $generator,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // TODO: Transform command data to value objects
        // Example:
        // $<?php echo $entity_snake; ?>Id = new <?php echo $entity; ?>Id($this->generator::generate());
        // $title = new Title($command->title);
        // $content = new Content($command->content);
        
        // TODO: Implement domain operation
        // Example for Create:
        // $<?php echo $entity_snake; ?> = new <?php echo $entity; ?>(
        //     $<?php echo $entity_snake; ?>Id,
        //     // ... other value objects
        // );
        // $this->repository->save($<?php echo $entity_snake; ?>);
        
        // Example for Update:
        // $<?php echo $entity_snake; ?> = $this->repository->find(new <?php echo $entity; ?>Id($command->id));
        // $<?php echo $entity_snake; ?>->update(...);
        // $this->repository->save($<?php echo $entity_snake; ?>);
        
        // Dispatch domain events if any
        // if ($<?php echo $entity_snake; ?>->hasUnreleasedEvents()) {
        //     foreach ($<?php echo $entity_snake; ?>->releaseEvents() as $event) {
        //         ($this->eventBus)($event);
        //     }
        // }
    }
}