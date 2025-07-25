<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\Shared\Model\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Category as DoctrineCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<DoctrineCategory>
 */
final class CategoryRepository extends ServiceEntityRepository implements CategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctrineCategory::class);
    }

    public function save(Category $category): void
    {
        $entity = $this->mapToEntity($category);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function findById(CategoryId $id): Category|null
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function existsById(CategoryId $id): bool
    {
        return null !== $this->find(Uuid::fromString($id->getValue()));
    }

    public function existsBySlug(CategorySlug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    public function existsWithSlug(Slug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    public function existsByName(CategoryName $name): bool
    {
        return null !== $this->findOneBy([
            'name' => $name->getValue(),
        ]);
    }

    /**
     * We intentionally override findAll to return domain models instead of entities
     *
     * @return Category[]
     *
     * @phpstan-ignore-next-line
     */
    #[\Override]
    public function findAll(): array
    {
        /** @var DoctrineCategory[] $entities */
        $entities = parent::findAll();

        return array_map([$this, 'mapToDomain'], $entities);
    }

    /**
     * @return Category[]
     */
    public function findByParentId(CategoryId|null $parentId): array
    {
        $criteria = [
            'parentId' => $parentId instanceof CategoryId ? Uuid::fromString($parentId->getValue()) : null,
        ];
        $entities = $this->findBy($criteria);

        return array_map([$this, 'mapToDomain'], $entities);
    }

    /**
     * @return Category[]
     */
    public function findRootCategories(): array
    {
        return $this->findByParentId(null);
    }

    public function delete(Category $category): void
    {
        $entity = $this->find(Uuid::fromString($category->id->getValue()));
        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    public function countArticlesByCategory(CategoryId $categoryId): int
    {
        // This will need to be implemented when we have the article-category relationship
        // For now, return 0 to allow category deletion
        return 0;
    }

    private function mapToEntity(Category $category): DoctrineCategory
    {
        $existingEntity = $this->find(Uuid::fromString($category->id->getValue()));

        if ($existingEntity) {
            // Update existing entity
            $existingEntity->setName($category->name->getValue());
            $existingEntity->setSlug($category->slug->getValue());
            $existingEntity->setDescription($category->description->getValue());
            $existingEntity->setParentId(
                $category->parentId instanceof CategoryId ? Uuid::fromString($category->parentId->getValue()) : null
            );
            $existingEntity->setOrder($category->order->getValue());
            $existingEntity->setUpdatedAt($category->updatedAt);

            return $existingEntity;
        }

        // Create new entity
        return new DoctrineCategory(
            id: Uuid::fromString($category->id->getValue()),
            name: $category->name->getValue(),
            slug: $category->slug->getValue(),
            description: $category->description->getValue(),
            parentId: $category->parentId instanceof CategoryId ? Uuid::fromString($category->parentId->getValue()) : null,
            order: $category->order->getValue(),
            createdAt: $category->createdAt,
            updatedAt: $category->updatedAt
        );
    }

    private function mapToDomain(DoctrineCategory $entity): Category
    {
        return new Category(
            id: new CategoryId($entity->getId()->toRfc4122()),
            name: new CategoryName($entity->getName()),
            slug: new CategorySlug($entity->getSlug()),
            description: new Description($entity->getDescription()),
            parentId: $entity->getParentId() instanceof Uuid ? new CategoryId($entity->getParentId()->toRfc4122()) : null,
            order: new Order($entity->getOrder()),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt()
        );
    }
}
