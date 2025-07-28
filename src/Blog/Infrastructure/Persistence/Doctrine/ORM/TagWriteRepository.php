<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Doctrine\ORM;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Tag as DoctrineTag;
use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends DoctrineRepository<DoctrineTag>
 *
 * @method DoctrineTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctrineTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctrineTag[] findAll()
 * @method DoctrineTag[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class TagWriteRepository extends DoctrineRepository implements TagWriteRepositoryInterface
{
    private const string ALIAS = 'tag';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, DoctrineTag::class, self::ALIAS);
    }

    #[\Override]
    public function add(Tag $tag): void
    {
        $entity = new DoctrineTag(
            // Store the ULID string directly as UUID type will convert it
            id: Uuid::fromString(substr($tag->getId()->getValue() . '00000000-0000-0000-0000-000000000000', 0, 36)),
            name: $tag->getName()->getValue(),
            slug: $tag->getSlug()->getValue(),
            articleCount: 0,
            createdAt: $tag->getCreatedAt(),
            updatedAt: $tag->getUpdatedAt()
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function update(Tag $tag): void
    {
        $entity = $this->find(Uuid::fromString($tag->getId()->getValue()));

        if (null === $entity) {
            throw new \RuntimeException(sprintf('Tag not found: %s', $tag->getId()->getValue()));
        }

        $entity->name = $tag->getName()->getValue();
        $entity->slug = $tag->getSlug()->getValue();
        $entity->updatedAt = $tag->getUpdatedAt();

        $this->getEntityManager()->flush();
    }

    #[\Override]
    public function get(TagId $tagId): Tag
    {
        $tag = $this->findById($tagId);

        if (!$tag instanceof Tag) {
            throw new \RuntimeException(sprintf('Tag not found: %s', $tagId->getValue()));
        }

        return $tag;
    }

    #[\Override]
    public function remove(Tag $tag): void
    {
        $entity = $this->find(Uuid::fromString($tag->getId()->getValue()));

        if (null !== $entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Map Doctrine entity to Tag aggregate
     */
    private function mapEntityToAggregate(DoctrineTag $doctrineTag): Tag
    {
        // Use reflection to recreate the aggregate since constructor might have business logic
        $reflectionClass = new \ReflectionClass(Tag::class);
        $tag = $reflectionClass->newInstanceWithoutConstructor();

        // Set properties using reflection
        $reflectionProperty = $reflectionClass->getProperty('tagId');
        $reflectionProperty->setAccessible(true);
        // If the ID is already a ULID format (26 chars), use it directly
        // Otherwise try to use the UUID string representation
        $idValue = $doctrineTag->id->toRfc4122();
        if (26 === strlen($idValue) && preg_match('/^[0123456789ABCDEFGHJKMNPQRSTVWXYZ]{26}$/', $idValue)) {
            $reflectionProperty->setValue($tag, new TagId($idValue));
        } else {
            // For UUID format, we need to store ULID instead
            // This is a temporary workaround - tags should be created with ULID from the start
            throw new \RuntimeException('Tag ID must be in ULID format, got UUID: ' . $idValue);
        }

        $nameProperty = $reflectionClass->getProperty('tagName');
        $nameProperty->setAccessible(true);
        $nameProperty->setValue($tag, new TagName($doctrineTag->name));

        $slugProperty = $reflectionClass->getProperty('tagSlug');
        $slugProperty->setAccessible(true);
        $slugProperty->setValue($tag, new TagSlug($doctrineTag->slug));

        $createdAtProperty = $reflectionClass->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($tag, $doctrineTag->createdAt);

        $updatedAtProperty = $reflectionClass->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($tag, $doctrineTag->updatedAt);

        // Initialize events array
        $eventsProperty = $reflectionClass->getProperty('events');
        $eventsProperty->setAccessible(true);
        $eventsProperty->setValue($tag, []);

        return $tag;
    }

    #[\Override]
    public function existsBySlugExcludingId(TagSlug $tagSlug, TagId $tagId): bool
    {
        $entity = $this->createQueryBuilder(self::ALIAS)
            ->where(sprintf('%s.slug = :slug', self::ALIAS))
            ->andWhere(sprintf('%s.id != :excludeId', self::ALIAS))
            ->setParameter('slug', $tagSlug->getValue())
            ->setParameter('excludeId', Uuid::fromString($tagId->getValue()))
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $entity;
    }

    #[\Override]
    public function findById(TagId $tagId): Tag|null
    {
        $entity = $this->find(Uuid::fromString($tagId->getValue()));

        if (null === $entity) {
            return null;
        }

        return $this->mapEntityToAggregate($entity);
    }

    #[\Override]
    public function findBySlug(TagSlug $tagSlug): Tag|null
    {
        $entity = $this->findOneBy([
            'slug' => $tagSlug->getValue(),
        ]);

        if (null === $entity) {
            return null;
        }

        return $this->mapEntityToAggregate($entity);
    }

    #[\Override]
    public function countArticles(TagId $tagId): int
    {
        // For now, return 0 as we don't have articles-tags relationship implemented
        // This would need to join with articles table when the relationship is established
        return 0;
    }

    /**
     * Check if tag exists by slug
     */
    public function existsBySlug(TagSlug $tagSlug): bool
    {
        return $this->findBySlug($tagSlug) instanceof Tag;
    }
}
