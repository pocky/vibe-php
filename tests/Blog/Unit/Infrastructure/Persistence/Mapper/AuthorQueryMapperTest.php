<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Infrastructure\Persistence\Mapper;

use App\Blog\Application\Shared\ReadModel\AuthorReadModel;
use App\Blog\Domain\Author\Shared\Model\Author as CreateAuthor;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;
use App\Blog\Infrastructure\Persistence\Mapper\AuthorQueryMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AuthorQueryMapperTest extends TestCase
{
    private AuthorQueryMapper $authorQueryMapper;

    protected function setUp(): void
    {
        $this->authorQueryMapper = new AuthorQueryMapper();
    }

    public function testMapCreatesAuthorReadModelFromDoctrineEntity(): void
    {
        // Arrange
        $uuidV7 = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 10:00:00');

        $author = new DoctrineAuthor(
            id: $uuidV7,
            name: 'John Doe',
            email: 'john@example.com',
            bio: 'Senior developer passionate about clean code',
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );

        // Act
        $authorReadModel = $this->authorQueryMapper->map($author);

        // Assert
        $this->assertInstanceOf(AuthorReadModel::class, $authorReadModel);
        $this->assertSame($uuidV7->toRfc4122(), $authorReadModel->id->getValue());
        $this->assertSame('John Doe', $authorReadModel->name->getValue());
        $this->assertSame('john@example.com', $authorReadModel->email->getValue());
        $this->assertSame('Senior developer passionate about clean code', $authorReadModel->bio->getValue());
        $this->assertEquals($createdAt, $authorReadModel->timestamps->getCreatedAt());
        $this->assertEquals($updatedAt, $authorReadModel->timestamps->getUpdatedAt());
    }

    public function testMapToCreateModelCreatesCreateAuthorFromDoctrineEntity(): void
    {
        // Arrange
        $uuidV7 = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 11:00:00');

        $author = new DoctrineAuthor(
            id: $uuidV7,
            name: 'Jane Smith',
            email: 'jane@example.com',
            bio: 'UX Designer with development background',
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );

        // Act
        $createModel = $this->authorQueryMapper->mapToCreateModel($author);

        // Assert
        $this->assertInstanceOf(CreateAuthor::class, $createModel);
        $this->assertSame($uuidV7->toRfc4122(), $createModel->id()->getValue());
        $this->assertSame('Jane Smith', $createModel->name()->getValue());
        $this->assertSame('jane@example.com', $createModel->email()->getValue());
        $this->assertSame('UX Designer with development background', $createModel->bio()->getValue());
        $this->assertEquals($createdAt, $createModel->createdAt());
        $this->assertEquals($updatedAt, $createModel->updatedAt()); // Mapper preserves the updatedAt from Doctrine entity

        // Should have no events when mapped from persistence (constructor doesn't record events)
        $this->assertEmpty($createModel->releaseEvents());
    }
}
