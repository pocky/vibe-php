<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Operation\Query\<?php echo $query_name; ?>\HandlerInterface;
use App\<?php echo $context; ?>\Application\Operation\Query\<?php echo $query_name; ?>\Query;
use App\<?php echo $context; ?>\Application\Operation\Query\<?php echo $query_name; ?>\View;
use App\<?php echo $context; ?>\Domain\Shared\Repository\<?php echo $entity; ?>RepositoryInterface;
<?php if (!$is_collection) { ?>
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;
<?php } ?>

final readonly class <?php echo $class_name; ?> implements HandlerInterface
{
    public function __construct(
        private <?php echo $entity; ?>RepositoryInterface $repository,
    ) {
    }

<?php if ($is_collection) { ?>
    public function __invoke(Query $query): View
    {
        // TODO: Implement collection query logic
        // Example:
        // $criteria = new ListCriteria(
        //     page: $query->page,
        //     limit: $query->limit,
        //     sortBy: $query->sortBy ?? 'createdAt',
        //     sortOrder: strtoupper($query->sortOrder ?? 'DESC'),
        // );
        
        // $data = ($this->lister)($criteria);
        
        // return new View(
        //     items: array_map(
        //         fn ($item) => new ItemView(
        //             id: $item->id->getValue(),
        //             // ... other fields
        //         ),
        //         $data->items
        //     ),
        //     total: $data->total,
        //     page: $data->page,
        //     limit: $data->limit
        // );
        
        return new View(
            items: [],
            total: 0,
            page: $query->page,
            limit: $query->limit,
        );
    }
<?php } else { ?>
    public function __invoke(Query $query): View
    {
        $<?php echo $entity_snake; ?>Id = new <?php echo $entity; ?>Id($query->id);
        $<?php echo $entity_snake; ?> = $this->repository->findById($<?php echo $entity_snake; ?>Id);

        if (!$<?php echo $entity_snake; ?> instanceof \App\<?php echo $context; ?>\Domain\Shared\Model\<?php echo $entity; ?>) {
            throw new \RuntimeException('<?php echo $entity; ?> not found');
        }

        return new View(
            id: $<?php echo $entity_snake; ?>->id->getValue(),
            // TODO: Map other fields
            createdAt: $<?php echo $entity_snake; ?>->createdAt,
            updatedAt: $<?php echo $entity_snake; ?>->updatedAt,
        );
    }
<?php } ?>
}
