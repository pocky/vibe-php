<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Category as DoctrineCategory;
use App\Blog\Infrastructure\Persistence\Mapper\CategoryQueryMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for read operations on categories.
 * Returns CategoryReadModel instances for query operations.
 *
 * @extends DoctrineRepository<DoctrineCategory>
 *
 * @method DoctrineCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineCategory[] findAll()
 * @method DoctrineCategory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class CategoryReadRepository extends DoctrineRepository implements CategoryReadRepositoryInterface
{
    private const string ALIAS = 'category';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly CategoryQueryMapper $categoryQueryMapper,
    ) {
        parent::__construct($managerRegistry, DoctrineCategory::class, self::ALIAS);
    }

    public function findById(CategoryId $categoryId): CategoryReadModel|null
    {
        $entity = $this->find($categoryId->getValue());

        return $entity ? $this->categoryQueryMapper->map($entity) : null;
    }

    public function findBySlug(CategorySlug $categorySlug): CategoryReadModel|null
    {
        $entity = $this->findOneBy([
            'slug' => $categorySlug->getValue(),
        ]);

        return $entity ? $this->categoryQueryMapper->map($entity) : null;
    }

    public function existsById(CategoryId $categoryId): bool
    {
        return null !== $this->find($categoryId->getValue());
    }

    public function existsBySlug(CategorySlug $categorySlug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $categorySlug->getValue(),
        ]);
    }

    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    public function existsByName(CategoryName $categoryName): bool
    {
        return null !== $this->findOneBy([
            'name' => $categoryName->getValue(),
        ]);
    }

    /**
     * @return CategoryReadModel[]
     *
     * @phpstan-ignore-next-line method.childReturnType
     */
    #[\Override]
    public function findAll(): array
    {
        $entities = $this->findBy([], [
            'order' => 'ASC',
            'name' => 'ASC',
        ]);

        return array_map($this->categoryQueryMapper->map(...), $entities);
    }

    /**
     * @return CategoryReadModel[]
     */
    public function findByParentId(CategoryId|null $parentId): array
    {
        $entities = $this->findBy(
            [
                'parentId' => $parentId?->getValue(),
            ],
            [
                'order' => 'ASC',
                'name' => 'ASC',
            ]
        );

        return array_map($this->categoryQueryMapper->map(...), $entities);
    }

    /**
     * @return CategoryReadModel[]
     */
    public function findRootCategories(): array
    {
        $entities = $this->findBy(
            [
                'parentId' => null,
            ],
            [
                'order' => 'ASC',
                'name' => 'ASC',
            ]
        );

        return array_map($this->categoryQueryMapper->map(...), $entities);
    }

    /**
     * @return array<array{category: CategoryReadModel, children: CategoryReadModel[]}>
     */
    public function findCategoryTree(): array
    {
        $rootCategories = $this->findRootCategories();
        $tree = [];

        foreach ($rootCategories as $rootCategory) {
            $children = $this->findByParentId($rootCategory->id);
            $tree[] = [
                'category' => $rootCategory,
                'children' => $children,
            ];
        }

        return $tree;
    }

    public function countArticlesByCategory(CategoryId $categoryId): int
    {
        // This would need to join with articles table
        // For now, return 0 as a placeholder implementation
        return 0;
    }

    /**
     * Filter categories by name pattern
     */
    public function withNameLike(string $pattern): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($pattern): void {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like(sprintf('LOWER(%s.name)', self::ALIAS), ':namePattern'))
                ->setParameter('namePattern', '%' . strtolower($pattern) . '%');
        });
    }

    /**
     * Filter categories by parent
     */
    public function withParent(CategoryId|null $parentId): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($parentId): void {
            if (!$parentId instanceof CategoryId) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull(sprintf('%s.parentId', self::ALIAS)));
            } else {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq(sprintf('%s.parentId', self::ALIAS), ':parentId'))
                    ->setParameter('parentId', $parentId->getValue());
            }
        });
    }

    /**
     * Filter root categories only
     */
    public function withRootOnly(): static
    {
        return $this->withParent(null);
    }

    /**
     * Sort by order and name
     */
    public function withDefaultOrdering(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder
                ->orderBy(sprintf('%s.order', self::ALIAS), 'ASC')
                ->addOrderBy(sprintf('%s.name', self::ALIAS), 'ASC');
        });
    }

    /**
     * Sort by name ascending
     */
    public function withNameAscending(): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy(sprintf('%s.name', self::ALIAS), 'ASC');
        });
    }

    /**
     * Search by query (name or slug)
     */
    public function withSearchQuery(string $query): static
    {
        return $this->filter(static function (QueryBuilder $queryBuilder) use ($query): void {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.name)', self::ALIAS), ':searchQuery'),
                        $queryBuilder->expr()->like(sprintf('LOWER(%s.slug)', self::ALIAS), ':searchQuery')
                    )
                )
                ->setParameter('searchQuery', '%' . strtolower($query) . '%');
        });
    }

    /**
     * Get results as CategoryReadModel array
     *
     * @return CategoryReadModel[]
     */
    public function getReadModels(): array
    {
        $entities = $this->getIterator();
        $results = [];

        foreach ($entities as $entity) {
            $results[] = $this->categoryQueryMapper->map($entity);
        }

        return $results;
    }
}
