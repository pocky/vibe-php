<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Domain\Create<?php echo $entity_class_name; ?>\Model\<?php echo $entity_class_name; ?> as Create<?php echo $entity_class_name; ?>;
use App\<?php echo $context; ?>\Domain\Shared\Repository\<?php echo $entity_class_name; ?>RepositoryInterface;
use App\<?php echo $context; ?>\Domain\Shared\ValueObject\<?php echo $entity_class_name; ?>Id;
use App\<?php echo $context; ?>\Infrastructure\Persistence\Doctrine\ORM\Entity\<?php echo $entity_class_name; ?> as Doctrine<?php echo $entity_class_name; ?>;
use App\<?php echo $context; ?>\Infrastructure\Persistence\Mapper\<?php echo $entity_class_name; ?>QueryMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Doctrine<?php echo $entity_class_name; ?>>
 */
final class <?php echo $class_name; ?> extends ServiceEntityRepository implements <?php echo $entity_class_name; ?>RepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly <?php echo $entity_class_name; ?>QueryMapper $queryMapper,
    ) {
        parent::__construct($registry, Doctrine<?php echo $entity_class_name; ?>::class);
    }

    #[\Override]
    public function add(Create<?php echo $entity_class_name; ?> $<?php echo $entity_variable; ?>): void
    {
        $entity = new Doctrine<?php echo $entity_class_name; ?>(
            id: Uuid::fromString($<?php echo $entity_variable; ?>->id()->getValue()),
            // TODO: Add other entity properties based on domain model
            createdAt: $<?php echo $entity_variable; ?>->createdAt(),
            updatedAt: $<?php echo $entity_variable; ?>->updatedAt(),
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function findById(<?php echo $entity_class_name; ?>Id $id): Create<?php echo $entity_class_name; ?>|null
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));

        return $entity ? $this->queryMapper->mapToCreateModel($entity) : null;
    }

    #[\Override]
    public function existsById(<?php echo $entity_class_name; ?>Id $id): bool
    {
        return null !== $this->find(Uuid::fromString($id->getValue()));
    }

    #[\Override]
    public function findAllPaginated(int $limit, int $offset): array
    {
        /** @var list<Doctrine<?php echo $entity_class_name; ?>> $entities */
        $entities = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var Create<?php echo $entity_class_name; ?>[] */
        return array_map($this->queryMapper->mapToCreateModel(...), $entities);
    }

    #[\Override]
    public function countAll(): int
    {
        /** @var int<0, max> */
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
