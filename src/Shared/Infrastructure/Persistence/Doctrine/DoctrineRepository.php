<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Infrastructure\Paginator\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Webmozart\Assert\Assert;

/**
 * @template T of object
 *
 * @extends ServiceEntityRepository<T>
 */
abstract class DoctrineRepository extends ServiceEntityRepository implements \IteratorAggregate
{
    protected QueryBuilder $queryBuilder;

    private int|null $_page = null;
    private int|null $_itemsPerPage = null;

    private int|null $page {
        get => $this->_page;
        set(int|null $value) {
            if (null !== $value) {
                Assert::positiveInteger($value);
            }
            $this->_page = $value;
        }
    }

    private int|null $itemsPerPage {
        get => $this->_itemsPerPage;
        set(int|null $value) {
            if (null !== $value) {
                Assert::positiveInteger($value);
            }
            $this->_itemsPerPage = $value;
        }
    }

    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        string $alias
    ) {
        parent::__construct($registry, $entityClass);

        $this->queryBuilder = $this->createQueryBuilder($alias);
    }

    public function withPagination(int $page, int $itemsPerPage): static
    {
        $cloned = clone $this;
        $cloned->page = $page;
        $cloned->itemsPerPage = $itemsPerPage;

        return $cloned;
    }

    public function withoutPagination(): static
    {
        $cloned = clone $this;
        $cloned->page = null;
        $cloned->itemsPerPage = null;

        return $cloned;
    }

    public function withPage(int $page): static
    {
        $cloned = clone $this;
        $cloned->page = $page;

        return $cloned;
    }

    public function withItemsPerPage(int $itemsPerPage): static
    {
        $cloned = clone $this;
        $cloned->itemsPerPage = $itemsPerPage;

        return $cloned;
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        $results = $this->queryBuilder->getQuery()->getResult();
        yield from $results;
    }

    public function paginator(): PaginatorInterface
    {
        $page = $this->page ?? 1;
        $itemsPerPage = $this->itemsPerPage ?? 10;

        // Clone query builder for count query
        $countQb = clone $this->queryBuilder;
        $countQb->select('COUNT(' . $countQb->getRootAliases()[0] . ')');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        // Apply pagination to main query
        $this->queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $items = $this->queryBuilder->getQuery()->getResult();

        return new readonly class($items, $total, $page, $itemsPerPage) implements PaginatorInterface {
            public function __construct(
                private array $items,
                private int $total,
                private int $page,
                private int $itemsPerPage,
            ) {
            }

            public function getItems(): array
            {
                return $this->items;
            }

            public function getTotalItems(): int
            {
                return $this->total;
            }

            public function getCurrentPage(): int
            {
                return $this->page;
            }

            public function getItemsPerPage(): int
            {
                return $this->itemsPerPage;
            }

            public function hasNextPage(): bool
            {
                return $this->page * $this->itemsPerPage < $this->total;
            }
        };
    }

    protected function filter(callable $filter): static
    {
        $cloned = clone $this;
        $filter($cloned->queryBuilder);

        return $cloned;
    }

    protected function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }
}
