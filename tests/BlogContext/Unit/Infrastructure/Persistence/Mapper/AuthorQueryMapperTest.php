<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Infrastructure\Persistence\Mapper;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthor;
use App\BlogContext\Domain\Shared\ReadModel\AuthorReadModel;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\Author as DoctrineAuthor;
use App\BlogContext\Infrastructure\Persistence\Mapper\AuthorQueryMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AuthorQueryMapperTest extends TestCase
{
    private AuthorQueryMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new AuthorQueryMapper();
    }

    public function testMapCreatesAuthorReadModelFromDoctrineEntity(): void
    {
        // Arrange
        $id = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 10:00:00');

        $entity = new DoctrineAuthor(
            id: $id,
            name: 'John Doe',
            email: 'john@example.com',
            bio: 'Senior developer passionate about clean code',
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );

        // Act
        $readModel = $this->mapper->map($entity);

        // Assert
        $this->assertInstanceOf(AuthorReadModel::class, $readModel);
        $this->assertEquals($id->toRfc4122(), $readModel->id->getValue());
        $this->assertEquals('John Doe', $readModel->name->getValue());
        $this->assertEquals('john@example.com', $readModel->email->getValue());
        $this->assertEquals('Senior developer passionate about clean code', $readModel->bio->getValue());
        $this->assertEquals($createdAt, $readModel->timestamps->getCreatedAt());
        $this->assertEquals($updatedAt, $readModel->timestamps->getUpdatedAt());
    }

    public function testMapToCreateModelCreatesCreateAuthorFromDoctrineEntity(): void
    {
        // Arrange
        $id = Uuid::v7();
        $createdAt = new \DateTimeImmutable('2024-01-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-20 11:00:00');

        $entity = new DoctrineAuthor(
            id: $id,
            name: 'Jane Smith',
            email: 'jane@example.com',
            bio: 'UX Designer with development background',
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );

        // Act
        $createModel = $this->mapper->mapToCreateModel($entity);

        // Assert
        $this->assertInstanceOf(CreateAuthor::class, $createModel);
        $this->assertEquals($id->toRfc4122(), $createModel->id()->getValue());
        $this->assertEquals('Jane Smith', $createModel->name()->getValue());
        $this->assertEquals('jane@example.com', $createModel->email()->getValue());
        $this->assertEquals('UX Designer with development background', $createModel->bio()->getValue());
        $this->assertEquals($createdAt, $createModel->createdAt());
        $this->assertEquals($createdAt, $createModel->updatedAt()); // Author::create() uses createdAt as initial updatedAt

        // Should have no events when mapped from persistence
        $this->assertEmpty($createModel->getEvents());
    }
}
