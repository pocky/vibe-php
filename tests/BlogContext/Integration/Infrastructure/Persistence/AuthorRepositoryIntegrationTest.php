<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Integration\Infrastructure\Persistence;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthor;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AuthorRepositoryIntegrationTest extends KernelTestCase
{
    private AuthorRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(AuthorRepository::class);

        // Clean up database before each test
        $this->entityManager->createQuery('DELETE FROM App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Author')->execute();
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean up after each test
        $this->entityManager->createQuery('DELETE FROM App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Author')->execute();
        $this->entityManager->flush();

        parent::tearDown();
    }

    public function testAddPersistsAuthorToDatabase(): void
    {
        // Arrange
        $author = CreateAuthor::create(
            id: new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            name: new AuthorName('John Doe'),
            email: new AuthorEmail('john@example.com'),
            bio: new AuthorBio('Senior developer passionate about clean code'),
            createdAt: new \DateTimeImmutable('2024-01-20 10:00:00')
        );

        // Act
        $this->repository->add($author);

        // Assert
        $this->entityManager->clear(); // Clear to force database fetch
        $foundAuthor = $this->repository->findById(new AuthorId('550e8400-e29b-41d4-a716-446655440000'));

        $this->assertNotNull($foundAuthor);
        $this->assertEquals('John Doe', $foundAuthor->name()->getValue());
        $this->assertEquals('john@example.com', $foundAuthor->email()->getValue());
        $this->assertEquals('Senior developer passionate about clean code', $foundAuthor->bio()->getValue());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        // Act
        $author = $this->repository->findById(new AuthorId('00000000-0000-0000-0000-000000000000'));

        // Assert
        $this->assertNull($author);
    }

    public function testFindByEmailFindsExistingAuthor(): void
    {
        // Arrange
        $author = CreateAuthor::create(
            id: new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            name: new AuthorName('Jane Smith'),
            email: new AuthorEmail('jane@example.com'),
            bio: new AuthorBio('UX Designer with development background'),
            createdAt: new \DateTimeImmutable('2024-01-20 10:00:00')
        );
        $this->repository->add($author);

        // Act
        $this->entityManager->clear();
        $foundAuthor = $this->repository->findByEmail(new AuthorEmail('jane@example.com'));

        // Assert
        $this->assertNotNull($foundAuthor);
        $this->assertEquals('Jane Smith', $foundAuthor->name()->getValue());
        $this->assertEquals('jane@example.com', $foundAuthor->email()->getValue());
    }

    public function testExistsByEmailReturnsTrueForExistingAuthor(): void
    {
        // Arrange
        $author = CreateAuthor::create(
            id: new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            name: new AuthorName('Bob Wilson'),
            email: new AuthorEmail('bob@example.com'),
            bio: new AuthorBio('Full-stack developer'),
            createdAt: new \DateTimeImmutable('2024-01-20 10:00:00')
        );
        $this->repository->add($author);

        // Act & Assert
        $this->assertTrue($this->repository->existsByEmail(new AuthorEmail('bob@example.com')));
        $this->assertFalse($this->repository->existsByEmail(new AuthorEmail('nonexistent@example.com')));
    }

    public function testFindAllPaginatedReturnsCorrectNumberOfAuthors(): void
    {
        // Arrange
        $this->createMultipleAuthors(5);

        // Act
        $authors = $this->repository->findAllPaginated(3, 1); // limit=3, offset=1

        // Assert
        $this->assertCount(3, $authors);
        $this->assertContainsOnlyInstancesOf(CreateAuthor::class, $authors);
    }

    public function testCountAllReturnsCorrectCount(): void
    {
        // Arrange
        $this->createMultipleAuthors(7);

        // Act
        $count = $this->repository->countAll();

        // Assert
        $this->assertEquals(7, $count);
    }

    private function createMultipleAuthors(int $count): void
    {
        for ($i = 1; $i <= $count; ++$i) {
            $author = CreateAuthor::create(
                id: new AuthorId(sprintf('550e8400-e29b-41d4-a716-%012d', $i)),
                name: new AuthorName("Author {$i}"),
                email: new AuthorEmail("author{$i}@example.com"),
                bio: new AuthorBio("Bio for author {$i}"),
                createdAt: new \DateTimeImmutable()
            );
            $this->repository->add($author);
        }
    }
}
