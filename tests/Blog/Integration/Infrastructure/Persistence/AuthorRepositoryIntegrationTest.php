<?php

declare(strict_types=1);

namespace App\Tests\Blog\Integration\Infrastructure\Persistence;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\AuthorWriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AuthorRepositoryIntegrationTest extends KernelTestCase
{
    private AuthorWriteRepository $authorRepository;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->authorRepository = static::getContainer()->get(AuthorWriteRepository::class);

        // Clean up database before each test
        $this->entityManager->createQuery('DELETE FROM App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author')->execute();
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean up after each test
        $this->entityManager->createQuery('DELETE FROM App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author')->execute();
        $this->entityManager->flush();

        parent::tearDown();
    }

    public function testAddPersistsAuthorToDatabase(): void
    {
        // Arrange
        $author = Author::create(
            new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Senior developer passionate about clean code')
        );

        // Act
        $this->authorRepository->add($author);

        // Assert
        $this->entityManager->clear(); // Clear to force database fetch
        $foundAuthor = $this->authorRepository->findById(new AuthorId('550e8400-e29b-41d4-a716-446655440000'));

        $this->assertInstanceOf(Author::class, $foundAuthor);
        $this->assertSame('John Doe', $foundAuthor->getName()->getValue());
        $this->assertSame('john@example.com', $foundAuthor->getEmail()->getValue());
        $this->assertSame('Senior developer passionate about clean code', $foundAuthor->getBio()->getValue());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        // Act
        $author = $this->authorRepository->findById(new AuthorId('00000000-0000-0000-0000-000000000000'));

        // Assert
        $this->assertNotInstanceOf(Author::class, $author);
    }

    public function testFindByEmailFindsExistingAuthor(): void
    {
        // Arrange
        $author = Author::create(
            new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new AuthorName('Jane Smith'),
            new AuthorEmail('jane@example.com'),
            new AuthorBio('UX Designer with development background')
        );
        $this->authorRepository->add($author);

        // Act
        $this->entityManager->clear();
        $foundAuthor = $this->authorRepository->findByEmail(new AuthorEmail('jane@example.com'));

        // Assert
        $this->assertInstanceOf(Author::class, $foundAuthor);
        $this->assertSame('Jane Smith', $foundAuthor->getName()->getValue());
        $this->assertSame('jane@example.com', $foundAuthor->getEmail()->getValue());
    }

    public function testExistsByEmailReturnsTrueForExistingAuthor(): void
    {
        // Arrange
        $author = Author::create(
            new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new AuthorName('Bob Wilson'),
            new AuthorEmail('bob@example.com'),
            new AuthorBio('Full-stack developer')
        );
        $this->authorRepository->add($author);

        // Act & Assert
        $this->assertTrue($this->authorRepository->existsByEmail(new AuthorEmail('bob@example.com')));
        $this->assertFalse($this->authorRepository->existsByEmail(new AuthorEmail('nonexistent@example.com')));
    }
}
