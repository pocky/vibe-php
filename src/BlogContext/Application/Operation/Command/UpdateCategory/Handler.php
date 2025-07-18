<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateCategory;

use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\Shared\Infrastructure\Generator\GeneratorInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private EventBusInterface $eventBus,
        private GeneratorInterface $generator,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // TODO: Transform command data to value objects
        // Example:
        // $categoryId = new CategoryId($this->generator::generate());
        // $title = new Title($command->title);
        // $content = new Content($command->content);

        // TODO: Implement domain operation
        // Example for Create:
        // $category = new Category(
        //     $categoryId,
        //     // ... other value objects
        // );
        // $this->repository->save($category);

        // Example for Update:
        // $category = $this->repository->find(new CategoryId($command->id));
        // $category->update(...);
        // $this->repository->save($category);

        // Dispatch domain events if any
        // if ($category->hasUnreleasedEvents()) {
        //     foreach ($category->releaseEvents() as $event) {
        //         ($this->eventBus)($event);
        //     }
        // }
    }
}
