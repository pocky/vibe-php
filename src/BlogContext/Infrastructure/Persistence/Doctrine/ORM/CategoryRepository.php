<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM;

use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategoryPath;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogCategory>
 */
final class CategoryRepository extends ServiceEntityRepository implements CategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogCategory::class);
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

    public function findBySlug(CategorySlug $slug): Category|null
    {
        $entity = $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function findByPath(CategoryPath $path): Category|null
    {
        $entity = $this->findOneBy([
            'path' => $path->getValue(),
        ]);

        return $entity ? $this->mapToDomain($entity) : null;
    }

    public function existsBySlug(CategorySlug $slug): bool
    {
        return null !== $this->findOneBy([
            'slug' => $slug->getValue(),
        ]);
    }

    public function existsByPath(CategoryPath $path): bool
    {
        return null !== $this->findOneBy([
            'path' => $path->getValue(),
        ]);
    }

    public function findRootCategories(): array
    {
        $entities = $this->createQueryBuilder('c')
            ->where('c.parentId IS NULL')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map($this->mapToDomain(...), $entities);
    }

    public function findChildrenByParentId(CategoryId $parentId): array
    {
        $entities = $this->createQueryBuilder('c')
            ->where('c.parentId = :parentId')
            ->setParameter('parentId', Uuid::fromString($parentId->getValue()))
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map($this->mapToDomain(...), $entities);
    }

    public function deleteById(CategoryId $id): void
    {
        $entity = $this->find(Uuid::fromString($id->getValue()));

        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Category[]
     */
    #[\Override]
    public function findAll(): array
    {
        $entities = $this->createQueryBuilder('c')
            ->orderBy('c.path', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map($this->mapToDomain(...), $entities);
    }

    private function mapToEntity(Category $category): BlogCategory
    {
        return new BlogCategory(
            id: Uuid::fromString($category->id()->getValue()),
            name: $category->name()->getValue(),
            slug: $category->slug()->getValue(),
            path: $category->path()->getValue(),
            parentId: $category->parentId() instanceof CategoryId ? Uuid::fromString($category->parentId()->getValue()) : null,
            level: $category->path()->getDepth(),
            description: null, // TODO: Add description to Category domain model
            articleCount: 0, // TODO: Implement article counting
            createdAt: $category->createdAt(),
            updatedAt: $category->updatedAt(),
        );
    }

    private function mapToDomain(BlogCategory $entity): Category
    {
        return new Category(
            id: new CategoryId($entity->getId()->toRfc4122()),
            name: new CategoryName($entity->getName()),
            slug: new CategorySlug($entity->getSlug()),
            path: new CategoryPath($entity->getPath()),
            parentId: $entity->getParentId() instanceof Uuid ? new CategoryId($entity->getParentId()->toRfc4122()) : null,
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt() ?? $entity->getCreatedAt(),
        );
    }
}
