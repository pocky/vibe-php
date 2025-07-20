<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Shared\Model\<?php echo $entity; ?>;
use App\<?php echo $context; ?>\Domain\Shared\Repository\<?php echo $entity; ?>RepositoryInterface;
<?php if (!$is_collection) { ?>
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity; ?>Id;
<?php } ?>
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        private <?php echo $entity; ?>RepositoryInterface $repository,
    ) {
    }

<?php if ($is_collection) { ?>
    /**
     * @return array<<?php echo $entity; ?>>
     */
    public function __invoke(Query $query): array
    {
        // TODO: Implement collection query logic
        // Example:
        // return $this->repository->findBy(
        //     criteria: ['status' => $query->status],
        //     orderBy: [$query->sortBy => $query->sortOrder],
        //     limit: $query->limit,
        //     offset: ($query->page - 1) * $query->limit
        // );
        
        return $this->repository->findAll();
    }
<?php } else { ?>
    public function __invoke(Query $query): <?php echo $entity . "\n"; ?>
    {
        $<?php echo $entity_snake; ?>Id = new <?php echo $entity; ?>Id($query->id);
        $<?php echo $entity_snake; ?> = $this->repository->findById($<?php echo $entity_snake; ?>Id);

        if (!$<?php echo $entity_snake; ?> instanceof <?php echo $entity; ?>) {
            throw new \RuntimeException('<?php echo $entity; ?> not found');
        }

        return $<?php echo $entity_snake; ?>;
    }
<?php } ?>
}
