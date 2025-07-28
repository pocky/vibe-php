<?php

declare(strict_types=1);

namespace App\Tests\Blog\Integration\Infrastructure\Persistence;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\TagWriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TagRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private TagWriteRepositoryInterface $tagRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->tagRepository = $container->get(TagWriteRepository::class);

        // Clear database using entity manager
        $this->entityManager->createQuery('DELETE FROM App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Tag')->execute();
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function test_it_adds_and_finds_tag_by_id(): void
    {
        $tag = Tag::create(
            new TagId('01J1234567890ABCDEFGHJKMNP'),
            new TagName('Technology'),
            new TagSlug('technology')
        );

        $this->tagRepository->add($tag);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundTag = $this->tagRepository->findById(new TagId('01J1234567890ABCDEFGHJKMNP'));

        $this->assertInstanceOf(Tag::class, $foundTag);
        $this->assertSame('01J1234567890ABCDEFGHJKMNP', $foundTag->id()->getValue());
        $this->assertSame('Technology', $foundTag->name()->getValue());
        $this->assertSame('technology', $foundTag->slug()->getValue());
    }

    public function test_it_finds_tag_by_slug(): void
    {
        $tag = Tag::create(
            new TagId('01J1234567890ABCDEFGHJKMNP'),
            new TagName('Technology'),
            new TagSlug('technology')
        );

        $this->tagRepository->add($tag);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundTag = $this->tagRepository->findBySlug(new TagSlug('technology'));

        $this->assertInstanceOf(Tag::class, $foundTag);
        $this->assertSame('technology', $foundTag->slug()->getValue());
    }

    public function test_it_checks_if_tag_exists_by_slug(): void
    {
        $tag = Tag::create(
            new TagId('01J1234567890ABCDEFGHJKMNP'),
            new TagName('Technology'),
            new TagSlug('technology')
        );

        $this->tagRepository->add($tag);
        $this->entityManager->flush();

        $this->assertTrue($this->tagRepository->existsBySlug(new TagSlug('technology')));
        $this->assertFalse($this->tagRepository->existsBySlug(new TagSlug('non-existent')));
    }

    public function test_it_finds_all_tags(): void
    {
        $tag1 = Tag::create(
            new TagId('01J1234567890ABCDEFGHJKMNP'),
            new TagName('Technology'),
            new TagSlug('technology')
        );

        $tag2 = Tag::create(
            new TagId('01J9876543210ZYXWVTSRQPNMH'),
            new TagName('Science'),
            new TagSlug('science')
        );

        $this->tagRepository->add($tag1);
        $this->tagRepository->add($tag2);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $tags = $this->tagRepository->findAll();

        $this->assertCount(2, $tags);
    }

    public function test_it_updates_tag(): void
    {
        $tag = Tag::create(
            new TagId('01J1234567890ABCDEFGHJKMNP'),
            new TagName('Technology'),
            new TagSlug('technology')
        );

        $this->tagRepository->add($tag);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Fetch the tag and update it
        $existingTag = $this->tagRepository->findById(new TagId('01J1234567890ABCDEFGHJKMNP'));
        $existingTag->update(
            new TagName('Tech'),
            new TagSlug('tech')
        );

        $this->tagRepository->add($existingTag);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundTag = $this->tagRepository->findById(new TagId('01J1234567890ABCDEFGHJKMNP'));

        $this->assertInstanceOf(Tag::class, $foundTag);
        $this->assertSame('Tech', $foundTag->name()->getValue());
        $this->assertSame('tech', $foundTag->slug()->getValue());
        // Article count is not part of the Tag model itself
    }

    public function test_it_removes_tag(): void
    {
        $tag = Tag::create(
            new TagId('01J1234567890ABCDEFGHJKMNP'),
            new TagName('Technology'),
            new TagSlug('technology')
        );

        $this->tagRepository->add($tag);
        $this->entityManager->flush();

        $this->tagRepository->remove($tag);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundTag = $this->tagRepository->findById(new TagId('01J1234567890ABCDEFGHJKMNP'));

        $this->assertNotInstanceOf(Tag::class, $foundTag);
    }

    // This test requires article-tag relationship to be implemented
    /*
    public function test_it_finds_tags_with_no_articles(): void
    {
        // TODO: Implement when article-tag relationship is added
    }
    */
}
