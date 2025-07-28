<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Category as DoctrineCategory;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends DoctrineRepository<DoctrineCategory>
 *
 * @method DoctrineCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineCategory[] findAll()
 * @method DoctrineCategory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class CategoryWriteRepository extends DoctrineRepository implements CategoryWriteRepositoryInterface
{
    private const string ALIAS = 'category';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, DoctrineCategory::class, self::ALIAS);
    }

    #[\Override]
    public function add(Category $category): void
    {
        $entity = new DoctrineCategory(
            id: Uuid::fromString($category->getId()->getValue()),
            name: $category->getName()->getValue(),
            slug: $category->getSlug()->getValue(),
            description: $category->getDescription()->getValue(),
            parentId: $category->getParentId() instanceof CategoryId ? Uuid::fromString($category->getParentId()->getValue()) : null,
            order: $category->getOrder()->getValue(),
            createdAt: $category->getCreatedAt(),
            updatedAt: $category->getUpdatedAt(),
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function update(Category $category): void
    {
        $entity = $this->find(Uuid::fromString($category->getId()->getValue()));

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Category not found: %s', $category->getId()->getValue()));
        }

        $entity->name = $category->getName()->getValue();
        $entity->slug = $category->getSlug()->getValue();
        $entity->description = $category->getDescription()->getValue();
        $entity->parentId = $category->getParentId() instanceof CategoryId ? Uuid::fromString($category->getParentId()->getValue()) : null;
        $entity->order = $category->getOrder()->getValue();
        $entity->updatedAt = $category->getUpdatedAt();

        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function remove(Category $category): void
    {
        $entity = $this->find(Uuid::fromString($category->getId()->getValue()));

        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    #[\Override]
    public function get(CategoryId $categoryId): Category
    {
        $entity = $this->find(Uuid::fromString($categoryId->getValue()));

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Category not found: %s', $categoryId->getValue()));
        }

        return $this->mapEntityToAggregate($entity);
    }

    /**
     * Map Doctrine entity to Category aggregate
     */
    private function mapEntityToAggregate(DoctrineCategory $doctrineCategory): Category
    {
        // Use reflection to recreate the aggregate since constructor might have business logic
        $reflectionClass = new \ReflectionClass(Category::class);
        $category = $reflectionClass->newInstanceWithoutConstructor();

        // Set properties using reflection
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($category, new CategoryId($doctrineCategory->id->toRfc4122()));

        $nameProperty = $reflectionClass->getProperty('name');
        $nameProperty->setAccessible(true);
        $nameProperty->setValue($category, new CategoryName($doctrineCategory->name));

        $slugProperty = $reflectionClass->getProperty('slug');
        $slugProperty->setAccessible(true);
        $slugProperty->setValue($category, new CategorySlug($doctrineCategory->slug));

        $descriptionProperty = $reflectionClass->getProperty('description');
        $descriptionProperty->setAccessible(true);
        $descriptionProperty->setValue($category, new Description($doctrineCategory->description ?? ''));

        $parentIdProperty = $reflectionClass->getProperty('parentId');
        $parentIdProperty->setAccessible(true);
        $parentIdProperty->setValue(
            $category,
            $doctrineCategory->parentId instanceof Uuid ? new CategoryId($doctrineCategory->parentId->toRfc4122()) : null
        );

        $orderProperty = $reflectionClass->getProperty('order');
        $orderProperty->setAccessible(true);
        $orderProperty->setValue($category, new Order($doctrineCategory->order));

        $createdAtProperty = $reflectionClass->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($category, $doctrineCategory->createdAt);

        $updatedAtProperty = $reflectionClass->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($category, $doctrineCategory->updatedAt);

        // Initialize events array
        $eventsProperty = $reflectionClass->getProperty('events');
        $eventsProperty->setAccessible(true);
        $eventsProperty->setValue($category, []);

        return $category;
    }

    #[\Override]
    public function existsById(CategoryId $categoryId): bool
    {
        return null !== $this->find(Uuid::fromString($categoryId->getValue()));
    }

    #[\Override]
    public function existsBySlug(CategorySlug $categorySlug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $categorySlug->getValue(),
        ]);
    }

    #[\Override]
    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    #[\Override]
    public function existsByName(CategoryName $categoryName): bool
    {
        return null !== $this->findOneBy([
            'name' => $categoryName->getValue(),
        ]);
    }

    public function countArticlesByCategory(CategoryId $categoryId): int
    {
        // For now, return 0 as we don't have articles table referenced
        // This should be implemented when article-category relationship is established
        return 0;
    }

    public function existsBySlugExcludingId(Slug $slug, CategoryId $categoryId): bool
    {
        $entity = $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->andWhere('c.id != :excludeId')
            ->setParameter('slug', $slug->getValue())
            ->setParameter('excludeId', Uuid::fromString($categoryId->getValue()))
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $entity;
    }

    public function countChildren(CategoryId $categoryId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.parentId = :parentId')
            ->setParameter('parentId', Uuid::fromString($categoryId->getValue()))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
